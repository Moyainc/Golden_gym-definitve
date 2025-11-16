<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailMasivoController extends Controller
{
    public function create()
    {
        return view('admin.mail_masivo');
    }
}
