<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Models\AuditLog;
use App\Models\Delivery;
use App\Models\Notification;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * search for all deliveries from the linked condominium
     * @param string $id_selected_condominium
     */
    public function index(Request $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $perPage = $request->query('perPage', 7);
        $page = $request->query('page', 1);

        $filters = $request->only(['global', 'status']);

        $query = Delivery::with(['user', 'employee'])
            ->where('condominium_id', $condominiumId)
            ->orderBy('received_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['global'])) {
            $search = $filters['global'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('item_description', 'like', "%{$search}%");
            });
        }

        $deliveries = $query->paginate($perPage, ['*'], 'page', $page);

        $deliveries->getCollection()->transform(function ($delivery) {
            return $this->formatDeliveryToResponse($delivery);
        });

        $summary = [];

        foreach (['pendente', 'entregue', 'devolvido'] as $key) {
            $countItens = Delivery::where('condominium_id', $condominiumId)
                ->where('status', $key)
                ->count();

            $field = 'total' . ucfirst($key);
            $summary[$field] = $countItens;
        } 

        $summary['total'] = Delivery::where('condominium_id', $condominiumId)->count();


        return response()->json([
            'data' => $deliveries->items(),
            'pagination' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
            ],
            'summary' => $summary 
        ]);
    }

    /**
     * Store a newly created Delivery and associate it with the selected condominium.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DeliveryRequest $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $delivery = Delivery::create($request->validated());

        $delivery->load(['user', 'employee']);
        $item_description = $delivery->item_description;

        if($request->input('user_id')) {
            Notification::create([
                'user_id' => $request->input('user_id'),
                'condominium_id' => $request->input('condominium_id'),
                'type' => Notification::TYPE_ENTREGA,
                'title' => "Nova entrega: $item_description",
                'message' => "Olá! Sua entrega ($item_description) chegou na portaria e está disponível para retirada.",
                'related_id' => $delivery->id
            ]);
        }

        AuditLog::deliveryLog(
            $request->user(), 
            $condominiumId, 
            AuditLog::ADD_DELIVERY,
            $delivery->item_description,
        );

        return response()->json([
            'message' => 'Entrega registrada com sucesso.',
            'data' => $this->formatDeliveryToResponse($delivery)
        ]);
    }

    /**
     * Update Delivery
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DeliveryRequest $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $data = $request->validated();

        $delivery = Delivery::where('id', $data['id'])
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$delivery) {
            return response()->json([
                'message' => 'Entrega não encontrada.'
            ], 404);
        }

        $originalData = $delivery->toArray();

        $delivery->update($data);
        $delivery->load(['user', 'employee']);

        AuditLog::deliveryLog(
            $request->user(), 
            $condominiumId, 
            AuditLog::UPDATED_DELIVERY,
            $delivery->item_description,
            ['before' => $originalData, 'after' => $delivery->toArray()]
        );

        return response()->json([
            'message' => 'Entrega atualizada com sucesso.',
            'data' => $this->formatDeliveryToResponse($delivery)
        ]);
    }

    /**
     * Get Delivery by id
     * 
     * @param $Id 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById(Request $request, string $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $delivery = Delivery::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$delivery) {
            return response()->json([
                'message' => 'Entrega não encontrada.'
            ], 404);
        }

        $delivery->load(['user', 'employee']);
        return response()->json($this->formatDeliveryToResponse($delivery));
    }

    /**
     * Delete Delivery
     * 
     * @param string $id Delivery id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $delivery = Delivery::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$delivery) {
            return response()->json([
                'message' => 'Entrega não encontrada.'
            ], 404);
        }

        $delivery->delete();

        AuditLog::deliveryLog(
            $request->user(), 
            $condominiumId, 
            AuditLog::DELETED_DELIVERY,
            $delivery->item_description,
        );

        return response()->json([
            'message' => 'Registro de entrega excluído com sucesso.'
        ]);
    }

    /**
     * Update Delivery status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(DeliveryRequest $request, string $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $delivery = Delivery::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$delivery) {
            return response()->json([
                'message' => 'Registro de entrega não encontrado.'
            ], 404);
        }

        $originalData = $delivery->toArray();

        $data = $request->validated();

        $delivery->update($data);
        $delivery->load(['user', 'employee']);

        AuditLog::deliveryLog(
            $request->user(), 
            $condominiumId, 
            AuditLog::UPDATED_DELIVERY,
            $delivery->item_description,
            ['before' => $originalData, 'after' => $delivery->toArray()]
        );

        return response()->json([
            'message' => 'Status da entrega atualizado com sucesso.',
            'data' => $this->formatDeliveryToResponse($delivery)
        ]);
    }

    /**
     * Mark the delivery as received by the resident
     * 
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsReceivedByResident(Request $request, string $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $user = $request->user();

        $delivery = Delivery::where('id', $id)
            ->where('user_id', $user->id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$delivery) {
            return response()->json([
                'message' => 'Registro de entrega não encontrado.'
            ], 404);
        }

        $originalData = $delivery->toArray();

        $delivery->status = 'entregue';
        $delivery->save();

        AuditLog::deliveryLog(
            $request->user(), 
            $condominiumId, 
            AuditLog::CONFIRMED_DELIVERY,
            $delivery->item_description,
            ['before' => $originalData, 'after' => $delivery->toArray()]
        );

        return response()->json([
            'message' => 'Sua entrega foi marcada como entregue.'
        ]);
    }

    private function formatDeliveryToResponse(Delivery $delivery)
    {
        return [
            'id' => $delivery->id,
            'condominium_id' =>$delivery->condominium_id,
            'item_description' => $delivery->item_description,
            'status' => $delivery->status,
            'received_at' => $delivery->received_at,
            'delivered_to_name' => $delivery->delivered_to_name,
            'delivered_at' => $delivery->delivered_at,
            'notes' => $delivery->notes,
            'user_name' => $delivery->user->name ?? null,
            'user_last_name' => $delivery->user->last_name ?? null,
            'user_id' => $delivery->user->id ?? null,
            'employee_name' => $delivery->employee->name ?? null,
            'employee_id' => $delivery->employee->id ?? null,
            'created_at' => $delivery->created_at,
            'updated_at' => $delivery->updated_at
        ];
    }
}
