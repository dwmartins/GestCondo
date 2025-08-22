<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryRequest;
use App\Models\Delivery;
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

        $deliveries = Delivery::with(['user', 'employee'])
            ->where('condominium_id', $condominiumId)
            ->orderBy('received_at', 'desc')
            ->get();

        $deliveriesFormatted = $deliveries->map(function ($delivery) {
            return $this->formatDeliveryToResponse($delivery);
        });

        return response()->json($deliveriesFormatted);
    }

    /**
     * Store a newly created Delivery and associate it with the selected condominium.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DeliveryRequest $request)
    {
        $delivery = Delivery::create($request->validated());

        $delivery->load(['user', 'employee']);

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
        $data = $request->validated();

        $delivery = Delivery::find($data['id']);

        if(!$delivery) {
            return response()->json([
                'message' => 'Entrega não encontrada.'
            ], 404);
        }

        $delivery->update($data);
        $delivery->load(['user', 'employee']);

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
    public function getById(string $id)
    {
        $delivery = Delivery::find($id);

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
    public function destroy(string $id)
    {
        $delivery = Delivery::find($id);

        if(!$delivery) {
            return response()->json([
                'message' => 'Entrega não encontrada.'
            ], 404);
        }

        $delivery->delete();

        return response()->json([
            'message' => 'Registro de entrega excluído com sucesso.'
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
