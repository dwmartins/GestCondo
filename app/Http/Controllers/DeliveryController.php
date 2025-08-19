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
            return [
                'id' => $delivery->id,
                'item_description' => $delivery->item_description,
                'status' => $delivery->status,
                'received_at' => $delivery->received_at,
                'delivered_to_name' => $delivery->delivered_to_name,
                'delivered_at' => $delivery->delivered_at,
                'notes' => $delivery->notes,
                'user_name' => $delivery->user->name ?? null,
                'employee_name' => $delivery->employee->name ?? null,
            ];
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

        return response()->json([
            'message' => 'Entrega registrada com sucesso.',
            'data' => $delivery
        ]);
    }
}
