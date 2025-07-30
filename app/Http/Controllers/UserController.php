<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validCondominium = $this->ensureValidCondominiumId($request);

        if($validCondominium['error']) {
            return response()->json([
                'message' => $validCondominium['message']
            ], $validCondominium['statusCode']);
        }

        $condominiumId = $validCondominium['condominiumId'];

        $users = User::whereHas('condominiums', function ($query) use ($condominiumId) {
            $query->where('condominium_id', $condominiumId);
        })->get();

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
        $validCondominium = $this->ensureValidCondominiumId($request);

        if($validCondominium['error']) {
            return response()->json([
                'message' => $validCondominium['message']
            ], $validCondominium['statusCode']);
        }

        $data = $request->validated();

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        $user->condominiums()->attach($validCondominium['condominiumId']);

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

        return response()->json($user);
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

            $filename = 'user_' . $user->id . '.webp';
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
     * Ensure that the X-Condominium-Id header exists and references a valid, active condominium.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Http\JsonResponse
     */
    private function ensureValidCondominiumId(Request $request) 
    {
        $condominiumId = $request->header('X-Condominium-Id');

        if(!$condominiumId) {
            return [
                'error'=> true,
                'message' => 'O cabeçalho "X-Condominium-Id" é obrigatório para realizar esta operação.',
                'statusCode' => 422
            ];
        }
        
        $condominiumExists = Condominium::where('id', $condominiumId)
            ->exists();

        if(!$condominiumExists) {
            return [
                'error'=> true,
                'message' => 'O condomínio informado não existe ou está inativo.',
                'statusCode' => 422
            ];
        }

        return [
            'error' => false,
            'condominiumId' => $condominiumId
        ];
    }
}
