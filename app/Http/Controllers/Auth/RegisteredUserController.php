<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        // Validasi dan buat user baru (dari class CreateNewUser)
        app(CreateNewUser::class)->create($request->all());

        // Jangan login otomatis
        // Redirect ke halaman konfirmasi
        return redirect()->route('register.waiting');
    }
}
