<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommonSpaceRequest;
use App\Models\AuditLog;
use App\Models\CommonSpace;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        $this->processAndSavePhoto($request, $commonSpace);

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

        $this->processAndSavePhoto($request, $commonSpace);

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

    /**
     * Process and save a photo for a common space.
     *
     * This method resizes and crops the image to 1024x768 if it is larger,
     * converts it to WebP format, saves it to storage, and updates the common space record.
     * If the common space already has a photo, the old one is deleted.
     *
     * @param Request $request The HTTP request containing the uploaded photo.
     * @param CommonSpace $commonSpace The common space model to update.
     * @return void
     */
    public function processAndSavePhoto(Request $request, CommonSpace $commonSpace)
    {
        if (!$request->hasFile('photo')) {
            return;
        }

        $imageManager = new ImageManager(new Driver());
        $oldImage = $commonSpace->photo ?? null;

        $photo = $request->file('photo');

        $fileName = "common_space_{$commonSpace->id}_" . time() . ".webp";
        $path = CommonSpace::PHOTO_PATH . "/{$fileName}";

        $image = $imageManager->read($photo->getRealPath());

        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $targetWidth = 1024;
        $targetHeight = 768;

        if ($imageWidth > $targetWidth || $imageHeight > $targetHeight) {
            $ratio = max($targetWidth / $imageWidth, $targetHeight / $imageHeight);

            $image->resize(
                intval($imageWidth * $ratio),
                intval($imageHeight * $ratio)
            );

            $x = intval(($image->width() - $targetWidth) / 2);
            $y = intval(($image->height() - $targetHeight) / 2);

            $image->crop($targetWidth, $targetHeight, $x, $y);
        }

        $webpImage = $image->toWebp(70);

        Storage::disk('public')->put($path, $webpImage);

        $commonSpace->photo = $fileName;
        $commonSpace->save();

        if ($oldImage) {
            $oldImagePath = CommonSpace::PHOTO_PATH . "/{$oldImage}";
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }
    }

}
