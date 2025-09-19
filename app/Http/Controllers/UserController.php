<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvatarRequest;
use App\Http\Requests\UserRequest;
use App\Models\AuditLog;
use App\Models\Condominium;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $perPage = $request->query('perPage', 7);
        $page = $request->query('page', 1);

        $filters = $request->only(['global', 'account_status', 'role']);

        $query = User::query()
            ->where('condominium_id', $condominiumId)
            ->where('role', '!=', 'funcionario');

        if (!empty($filters['global'])) {
            $search = $filters['global'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if (isset($filters['account_status']) && $filters['account_status'] !== '') {
            $query->where('account_status', (int) $filters['account_status']);
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $totalUsers = User::where('condominium_id', $condominiumId)
            ->where('role', '!=', 'funcionario')
            ->count();

        $activeUsers = User::where('condominium_id', $condominiumId)
            ->where('role', '!=', 'funcionario')
            ->where('account_status', true)
            ->count();

        $inactiveUsers = User::where('condominium_id', $condominiumId)
            ->where('role', '!=', 'funcionario')
            ->where('account_status', false)
            ->count();

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'summary' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers
            ]
        ]);
    }

    /**
     * Store a newly created user and associate it with the selected condominium.
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $data = $request->validated();

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->condominium_id = $condominiumId;
        $user->save();

        if($user->role === User::ROLE_SUPORTE) {
            $user->condominium_id = null;
            $user->save();
        }

        if(in_array($user->role, [User::ROLE_SINDICO, User::ROLE_SUB_SINDICO])) {
            if (!$user->condominiums()->where('condominium_id', $condominiumId)->exists()) {
                $user->condominiums()->attach($condominiumId);
            }

            if($user->isSubSindico() && !UserPermission::where('user_id', $user->id)->exists()) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permissions' => UserPermission::defaultPermissions()
                ]);
            }
        }

        if($request->hasFile('avatar')) {
            $imageManager = new ImageManager(new Driver);

            $avatar = $request->file('avatar');

            $filename = 'user_' . $user->id . '.webp';
            $path = 'avatars/' . $filename;

            $image = $imageManager->read($avatar->getRealPath());
            $webpImage = $image->toWebp(70);

            Storage::disk('public')->put($path, $webpImage);

            $user->avatar = $filename;
            $user->save();
        }

        AuditLog::residentLog(
            $request->user(), 
            $condominiumId, 
            AuditLog::ADD_RESIDENT,
            $user->getFullName(), 
        );

        return response()->json([
            'message' => 'Morador adicionado com sucesso.',
            'data' => $user
        ]);
    }

    /**
     * Retrieve a user by ID.
     *
     * @param Request $request
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse JSON response with user data or error message
     */
    public function getById(Request $request, $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $user = User::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $permissions = UserPermission::where('user_id', $user->id)->first();

        $userData = $user->toArray();
        $userData['permissions'] = $permissions ? $permissions->permissions : [];

        return response()->json($userData);
    }

    /**
     * Update a user by ID.
     *
     * @param UserRequest $request Validated user data
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse JSON response with success message and updated user data or error message
     */
    public function update(UserRequest $request, $id)
    {
        $authUser = $request->user();
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $user = User::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if (!$user && $authUser->isSuporte() && $id === $authUser->id) {
            $user = $authUser;
        }

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $originalData = $user->toArray();
        $data = $request->validated();

        $user->update($data);

        if(!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        if($request->hasFile('avatar')) {
            $imageManager = new ImageManager(new Driver);

            $avatar = $request->file('avatar');

            $timestamp = now()->timestamp;
            $filename = 'user_' . $user->id . '_' . $timestamp . '.webp';
            $path = 'avatars/' . $filename;

            $image = $imageManager->read($avatar->getRealPath());
            $webpImage = $image->toWebp(70);

            Storage::disk('public')->put($path, $webpImage);

            $user->avatar = $filename;
            $user->save();
        }

        AuditLog::residentLog(
            $request->user(), 
            $condominiumId, 
            $request->user()->id === $user->id ? 'atualizou sua própria conta' : AuditLog::UPDATED_RESIDENT,
            $request->user()->id === $user->id ? null : $user->getFullName(), 
            ['before' => $originalData, 'after' => $user->toArray()]
        );

        return response()->json([
            'message' => 'Usuário atualizado com sucesso.',
            'data' => $user
        ]);
    }

    /**
     * Delete a user by ID, including their avatar file if it exists.
     * 
     * @param string $id The ID of the user to delete.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function destroy(Request $request, string $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $user = User::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        if($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $user->delete();

        if($request->user()->id !== $user->id) {
            AuditLog::residentLog(
                $request->user(), 
                $condominiumId, 
                AuditLog::DELETED_RESIDENT,
                $user->getFullName(), 
            );
        }

        return response()->json([
            'message' => 'Usuário excluído com sucesso.'
        ], 200);
    }

    /**
     * Updates user status and role
     * 
     * @param string $id
     * @param boolean $account_status
     * @param string $role
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function updateStatus(Request $request, string $id)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');
        $user = User::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $originalData = $user->toArray();

        $user->account_status = $request->input('account_status');
        $user->accepts_emails = $request->input('accepts_emails');
        $user->role = $request->input('role');
        $user->save();

        if($user->role === User::ROLE_SUPORTE) {
            $user->condominium_id = null;
            $user->save();
        }

        if(in_array($user->role, [User::ROLE_SINDICO, User::ROLE_SUB_SINDICO])) {
            if (!$user->condominiums()->where('condominium_id', $condominiumId)->exists()) {
                $user->condominiums()->attach($condominiumId);
            }

            if($user->isSubSindico()) {
                $permission = UserPermission::firstOrNew([
                    'user_id' => $user->id
                ]);

                if ($request->has('permissions')) {
                    $frontPermissions = $request->input('permissions');

                    $merged = $this->mergePermissions(
                        UserPermission::defaultPermissions(),
                        $frontPermissions
                    );

                    $permission->permissions = $merged;
                    $permission->save();

                } elseif (empty($permission->permissions)) {
                    $permission->permissions = UserPermission::defaultPermissions();
                    $permission->save();

                } else {
                    $merged = $this->mergePermissions(
                        UserPermission::defaultPermissions(),
                        $permission->permissions
                    );

                    $permission->permissions = $merged;
                    $permission->save();
                }
            }
        }

        AuditLog::residentLog(
            $request->user(), 
            $condominiumId, 
            $request->user()->id === $user->id ? 'atualizou sua própria conta' : AuditLog::UPDATED_RESIDENT,
            $request->user()->id === $user->id ? null : $user->getFullName(), 
            ['before' => $originalData, 'after' => $user->toArray()]
        );

        return response()->json([
            'message' => 'Alterações salvas com sucesso',
            'user' => $user
        ]);
    }

    /**
     * Change user avatar
     * 
     * @param string $id
     * @param file $avatar
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function changeAvatar(AvatarRequest $request, $id)
    {
        $id = (int) $id;
        $authUser = $request->user();
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $avatar = $request->file('avatar');

        $user = User::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if (!$user && $authUser->isSuporte() && $id === $authUser->id) {
            $user = $authUser;
        }

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $originalData = $user->toArray();

        $imageManager = new ImageManager(new Driver);

        $avatar = $request->file('avatar');

        $timestamp = now()->timestamp;
        $filename = 'user_' . $user->id . '_' . $timestamp . '.webp';
        $path = 'avatars/' . $filename;

        $image = $imageManager->read($avatar->getRealPath());
        $webpImage = $image->toWebp(70);

        Storage::disk('public')->put($path, $webpImage);

        $user->update([
            'avatar' => $filename,
            'updated_at' => now()
        ]);

        AuditLog::residentLog(
            $request->user(), 
            $condominiumId, 
            $request->user()->id === $user->id ? 'atualizou sua própria conta' : AuditLog::UPDATED_RESIDENT,
            $request->user()->id === $user->id ? null : $user->getFullName(), 
            ['before' => $originalData, 'after' => $user->toArray()]
        );

        return response()->json([
            'message' => 'Imagem alterada com sucesso.',
            'user' => $user
        ]);
    }

    /**
     * Change user settings
     * 
     * @param string $id
     * @param boolean $accepts_emails
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function changeSettings(Request $request, $id) 
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $user = User::where('id', $id)
            ->where('condominium_id', $condominiumId)
            ->first();

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $originalData = $user->toArray();

        $user->accepts_emails = $request->input('accepts_emails');
        $user->save();

        AuditLog::residentLog(
            $request->user(), 
            $condominiumId, 
            $request->user()->id === $user->id ? 'atualizou sua própria conta' : AuditLog::UPDATED_RESIDENT,
            $request->user()->id === $user->id ? null : $user->getFullName(), 
            ['before' => $originalData, 'after' => $user->toArray()]
        );

        return response()->json([
            'message' => 'Alterações salvas com sucesso.',
            'user' => $user
        ]);
    }

    /**
     * Retrieve all residents (users with role 'morador') 
     * from the selected condominium, returning only id, name and last name.
     * 
     * @return \Illuminate\Http\JsonResponse List of residents (id and name)
     */
    public function getUsersExceptSupportAndEmployee(Request $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $users = User::select('id', 'name', 'last_name')
            ->where('condominium_id', $condominiumId)
            ->whereNotIn('role', ['support', 'funcionario'])
            ->get();

        return response()->json($users);
    }

    protected function mergePermissions(array $defaults, array $current): array
    {
        foreach ($defaults as $section => $actions) {
            if (!isset($current[$section])) {
                $current[$section] = $actions;
                continue;
            }

            foreach ($actions as $action => $value) {
                if (!array_key_exists($action, $current[$section])) {
                    $current[$section][$action] = $value;
                }
            }
        }

        return $current;
    }

}
