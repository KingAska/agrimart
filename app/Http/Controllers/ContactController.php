<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessage;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $adminEmail = env('CONTACT_SUPPORT_EMAIL', 'azz141@gmail.com');
        Mail::to($adminEmail)->send(new ContactMessage($validated));

        return back()->with('success', 'Pesan Anda berhasil dikirim! Kami akan segera merespons via Email.');
    }
}