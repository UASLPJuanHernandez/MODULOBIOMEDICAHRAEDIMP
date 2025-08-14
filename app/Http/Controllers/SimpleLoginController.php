<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SimpleLoginController extends Controller
{
    public function showLoginForm()
    {
        // Si ya está autenticado, redirigir al admin
        if (Auth::check()) {
            return redirect('/admin');
        }
        
        return view('simple-login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            
            // Verificar que el usuario está autenticado
            if (Auth::check()) {
                // Log de debug
                \Log::info('Usuario autenticado exitosamente: ' . Auth::user()->email);
                
                // Redirigir al panel de administración
                return redirect('/admin')->with('success', 'Acceso autorizado correctamente');
            }
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no son correctas.',
        ])->withInput($request->except('password'));
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/simple-login');
    }
}
