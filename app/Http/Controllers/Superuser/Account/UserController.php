<?php

namespace App\Http\Controllers\Superuser\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('superuser.account.user.index');
    }
}
