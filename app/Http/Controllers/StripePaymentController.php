<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Prenotazione;
use App\Models\Campi;

class StripePaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'campi_id' => 'required',
            'user_id' => 'required',
            'data' => 'required|date',
            'ora' => 'required',
            'prezzo' => 'required'
        ]);

        // Vérifier si déjà réservé
        $exists = Prenotazione::where('campi_id', $request->campi_id)
            ->where('data', $request->data)
            ->where('ora', $request->ora)
            ->first();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Questo orario è già prenotato.'
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Convertir en centimes
        $amount = $request->prezzo * 100;

        $paymentIntent = PaymentIntent::create([
            'amount'   => $amount,
            'currency' => 'eur',
            'metadata' => [
                'campi_id' => $request->campi_id,
                'user_id'  => $request->user_id,
                'data'     => $request->data,
                'ora'      => $request->ora,
            ],
        ]);

        // Créer la réservation (pending)
        $prenotazione = Prenotazione::create([
            'campi_id' => $request->campi_id,
            'user_id' => $request->user_id,
            'data' => $request->data,
            'ora' => $request->ora,
            'prezzo' => $request->prezzo,
            'payment_status' => 'pending',
            'payment_intent_id' => $paymentIntent->id
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'prenotazione_id' => $prenotazione->id
        ]);
    }


    // WEBHOOK STRIPE
    public function handleWebhook(Request $request)
    {
        $event = $request->all();

        if ($event['type'] === "payment_intent.succeeded") {

            $pi = $event["data"]["object"]["id"];

            $prenotazione = Prenotazione::where('payment_intent_id', $pi)->first();

            if ($prenotazione) {
                $prenotazione->update([
                    'payment_status' => 'paid'
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
