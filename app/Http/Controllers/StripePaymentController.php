<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Prenotazione;
use Carbon\Carbon;

class StripePaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'campi_id' => 'required|integer',
            'user_id' => 'required|integer',
            'data' => 'required|date',
            'ora' => 'required|string',
            'prezzo' => 'required|numeric'
        ]);

        // Vérifier si le créneau est déjà réservé par quelqu'un
        $slotTaken = Prenotazione::where('campi_id', $request->campi_id)
            ->where('data', $request->data)
            ->where('ora', $request->ora)
            ->where('payment_status', 'paid')
            ->first();

        if ($slotTaken) {
            return response()->json([
                'status' => false,
                'message' => 'Questo orario è già prenotato.'
            ], 400);
        }

        // Vérifier si l'utilisateur a déjà une réservation pour ce créneau
        $userSlot = Prenotazione::where('campi_id', $request->campi_id)
            ->where('user_id', $request->user_id)
            ->where('data', $request->data)
            ->where('ora', $request->ora)
            ->first();

        if ($userSlot && $userSlot->payment_status == 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Hai già prenotato e pagato questo orario.'
            ], 400);
        }

        // Nettoyage des pending > 10 min
        Prenotazione::where('payment_status', 'pending')
            ->where('created_at', '<', Carbon::now()->subMinutes(10))
            ->delete();

        // Si il existe un pending, réutiliser le PaymentIntent
        if ($userSlot && $userSlot->payment_status == 'pending') {
            Stripe::setApiKey(config('services.stripe.secret'));
            $pi = PaymentIntent::retrieve($userSlot->payment_intent_id);

            return response()->json([
                'client_secret' => $pi->client_secret,
                'prenotazione_id' => $userSlot->id
            ]);
        }

        // Créer nouveau PaymentIntent
        Stripe::setApiKey(config('services.stripe.secret'));
        $amount = $request->prezzo * 100;

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'eur',
            'metadata' => [
                'campi_id' => $request->campi_id,
                'user_id' => $request->user_id,
                'data' => $request->data,
                'ora' => $request->ora,
            ],
        ]);

        // Créer réservation pending
        $prenotazione = Prenotazione::create([
            'campi_id' => $request->campi_id,
            'user_id' => $request->user_id,
            'data' => $request->data,
            'ora' => $request->ora,
            'prezzo' => $request->prezzo,
            'payment_status' => 'paid',
            'payment_intent_id' => $paymentIntent->id
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'prenotazione_id' => $prenotazione->id
        ]);
    }

    // Webhook Stripe
    public function handleWebhook(Request $request)
    {
        $event = $request->all();

        if ($event['type'] === "payment_intent.succeeded") {
            $pi = $event["data"]["object"]["id"];
            $prenotazione = Prenotazione::where('payment_intent_id', $pi)->first();

            if ($prenotazione) {
                $prenotazione->update(['payment_status' => 'paid']);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
