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
