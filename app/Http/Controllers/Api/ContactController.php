<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'enquiry' => ['required', 'string'],
        ]);

        $toAddress = config('mail.contact_form.to.address');
        $toName = config('mail.contact_form.to.name');

        Mail::to($toAddress, $toName)
            ->send(
                (new ContactFormMail($validated))
                    ->replyTo($validated['email'], $validated['name'])
            );

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
        ]);
    }
}