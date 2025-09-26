<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    
    public function index()
    {
        
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'exists:roles,id'], 
        ]);

        $username = strtolower($request->username);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
        ]);

    
        $role = Role::findOrFail($request->roles);


    
        $user->assignRole($role);
   

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    
    public function show(string $id)
    {
        //
    }

    
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

   
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        $input = $request->except('password');
        if ($request->filled('password')) {
            $input['password'] = Hash::make($request->password);
        }

        $input['username'] = strtolower($request->username);

        $user->update($input);
    
    
       $roles = Role::whereIn('id', $request->roles)->get();

    
       $user->syncRoles($roles);
    
       return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }


    
    public function destroy(User $user) 
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}