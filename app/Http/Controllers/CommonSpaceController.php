<?php

namespace App\Http\Controllers;

use App\Models\CommonSpace;
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
}
