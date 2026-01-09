<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        return view('admin.register-user');
    }

    public function store(Request $request)
    {
        $request->validate([
            
            'nombre_completo' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'rol' => 'required|in:admin,maestro,alumno',
        ]);

        User::create([
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            // Agrega otros campos por defecto si son necesarios
        ]);

        return back()->with('success', 'Usuario registrado exitosamente');
    }
}