<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Send the contact form email.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'subject.required' => 'Le sujet est obligatoire.',
            'subject.max' => 'Le sujet ne peut pas dépasser 255 caractères.',
            'message.required' => 'Le message est obligatoire.',
            'message.max' => 'Le message ne peut pas dépasser 5000 caractères.',
        ]);

        try {
            Mail::to('thomasbourcy@live.com')->send(new ContactFormMail(
                name: $validated['name'],
                email: $validated['email'],
                phone: $validated['phone'] ?? null,
                subject: $validated['subject'],
                contactMessage: $validated['message'],
            ));

            Log::info('Contact form email sent successfully', [
                'from' => $validated['email'],
                'subject' => $validated['subject'],
            ]);

            return redirect()
                ->route('contact')
                ->with('success', 'Merci pour votre message ! Je vous répondrai dans les plus brefs délais.');
        } catch (\Exception $e) {
            Log::error('Error sending contact form email: ' . $e->getMessage(), [
                'from' => $validated['email'],
                'subject' => $validated['subject'],
            ]);

            return redirect()
                ->route('contact')
                ->with('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.')
                ->withInput();
        }
    }
}
