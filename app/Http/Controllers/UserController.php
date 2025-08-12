<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvatarRequest;
use App\Http\Requests\UserRequest;
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

        $users = User::query()
            ->where('condominium_id', $condominiumId)
            ->where('role', '!=', 'funcionario')
            ->get();

        return response()->json([
            'data' => $users
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

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
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
        $user = User::find($id);

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
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

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
    public function destroy(string $id)
    {
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        if($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $user->delete();

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
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

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
        $avatar = $request->file('avatar');

        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

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
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        $user->accepts_emails = $request->input('accepts_emails');
        $user->save();

        return response()->json([
            'message' => 'Alterações salvas com sucesso.',
            'user' => $user
        ]);
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
