<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);

        return $this->successResponse('Daftar pengguna berhasil diambil', UserResource::collection($users));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:25',
            'role' => 'required|in:customer,admin',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return $this->successResponse('Pengguna berhasil dibuat', new UserResource($user), 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return $this->successResponse('Detail pengguna berhasil diambil', new UserResource($user));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|max:25',
            'role' => 'sometimes|in:customer,admin',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'phone', 'role']));

        return $this->successResponse('Pengguna berhasil diperbarui', new UserResource($user));
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return $this->successResponse('Pengguna berhasil dihapus');
    }
}
