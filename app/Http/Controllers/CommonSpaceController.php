<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommonSpaceRequest;
use App\Models\AuditLog;
use App\Models\CommonSpace;
use App\Models\Condominium;
use Illuminate\Http\Request;

class CommonSpaceController extends Controller
{
    /**
     * search for all common spaces from the linked condominium
     * @param string $id_selected_condominium
     */
    public function index(Request $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $perPage = $request->query('perPage', 7);
        $page = $request->query('page', 1);

        $filters = $request->only(['global', 'status']);

        $query = CommonSpace::query()
            ->where('condominium_id', $condominiumId);
        
        if (!empty($filters['global'])) {
            $search = $filters['global'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (int) $filters['status']);
        }

        $commonSpaces = $query->paginate($perPage, ['*'], 'page', $page);

        $summary = [];
        $summary['total'] = CommonSpace::where('condominium_id', $condominiumId)->count();

        return response()->json([
            'data' => $commonSpaces->items(),
            'pagination' => [
                'current_page' => $commonSpaces->currentPage(),
                'last_page' => $commonSpaces->lastPage(),
                'per_page' => $commonSpaces->perPage(),
                'total' => $commonSpaces->total(),
            ],
            'summary' => $summary
        ]);
    }

    /**
     * Creates a new common area
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CommonSpaceRequest $request)
    {
        /** @var Condominium $condominium */
        $condominium = selectedCondominium();
        
        $name = $request->input('name');

        $exists = CommonSpace::where('condominium_id', $condominium->id)
            ->where('name', $name)
            ->first();
        
        if ($exists) {
            return response()->json([
                'message' => 'Já existe uma área comum com este nome no condomínio.',
                'data' => $exists,
            ], 422);
        }

        $commonSpace = CommonSpace::create($request->validated());

        AuditLog::commonSpaceLog(
            $request->user(), 
            $condominium->id, 
            AuditLog::ADD_COMMON_SPACE,
            $commonSpace->name,
        );

        return response()->json([
            'message' => 'Área comum adicionada com sucesso.',
            'data' => $commonSpace
        ]);
    }

    /**
     * Updates a common area
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CommonSpaceRequest $request)
    {
        /** @var Condominium $condominium */
        $condominium = selectedCondominium();

        $data = $request->validated();

        $commonSpace = CommonSpace::where('condominium_id', $condominium->id)
            ->where('id', $data['id'])
            ->first();

        if(!$commonSpace) {
            return response()->json([
                'message' => 'Área comum não encontrada.'
            ], 404);
        }
        
        $originalData = $commonSpace->toArray();
        $commonSpace->update($data);

        AuditLog::commonSpaceLog(
            $request->user(), 
            $condominium->id, 
            AuditLog::UPDATED_COMMON_SPACE,
            $commonSpace->name,
            ['before' => $originalData, 'after' => $commonSpace->toArray()]
        );

        return response()->json([
            'message' => 'Área comum atualizada com sucesso.'
        ]);
    }

    /**
     * Delete Common space
     * 
     * @param string $id Common space id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        /** @var Condominium $condominium */
        $condominium = selectedCondominium();

        $commonSpace = CommonSpace::where('condominium_id', $condominium->id)
            ->where('id', $id)
            ->first();
        
        if(!$commonSpace) {
            return response()->json([
                'message' => 'Área comum não encontrada.'
            ], 404);
        }

        $commonSpace->delete();

        AuditLog::commonSpaceLog(
            $request->user(), 
            $condominium->id, 
            AuditLog::DELETED_COMMON_SPACE,
            $commonSpace->name,
        );
        
        return response()->json([
            'message' => 'Área comum excluída com sucesso.'
        ]);
    }
}
