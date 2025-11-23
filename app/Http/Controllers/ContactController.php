<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            // Log the contact form data for now
            Log::info('Contact form submission:', $validated);

            // TODO: Configure mail settings and send actual email
            // For now, we'll just log the data and show a success message

            // Example mail sending (uncomment when mail is configured):
            /*
            Mail::send('emails.contact', $validated, function ($message) use ($validated) {
                $message->to('contact@lelaboratoirenumerique.com')
                    ->subject('Nouveau message de contact : ' . $validated['subject'])
                    ->replyTo($validated['email'], $validated['name']);
            });
            */

            return redirect()
                ->route('contact')
                ->with('success', 'Merci pour votre message ! Je vous répondrai dans les plus brefs délais.');
        } catch (\Exception $e) {
            Log::error('Error sending contact form: ' . $e->getMessage());

            return redirect()
                ->route('contact')
                ->with('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.')
                ->withInput();
        }
    }
}
