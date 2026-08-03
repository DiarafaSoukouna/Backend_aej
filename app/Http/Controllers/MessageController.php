<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Mail\SendMail;

Class MessageController extends Controller
{
 public function SendMailTo(Request $request)

    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        // Exemple de données dynamiques
        $recipient = $request->input('email');
        $subject = $request->input('subject');
        $message = $request->input('message');

        // Envoi du mail
        Mail::to($recipient)->send(new SendMail($subject, $message));

        return "Mail envoyé avec succès à $recipient !";
    }
}
