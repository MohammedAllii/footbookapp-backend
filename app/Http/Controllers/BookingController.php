<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prenotazione;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function getBookedHours(Request $request)
    {
        $request->validate([
            'campi_id' => 'required|integer',
            'data' => 'required|date',
        ]);

        // Récupérer les heures déjà réservées pour ce terrain et cette date
        $bookedHours = Prenotazione::where('campi_id', $request->campi_id)
            ->where('data', $request->data)
            ->where('payment_status', 'paid') // uniquement les paiements réussis
            ->pluck('ora');

        return response()->json([
            'status' => true,
            'booked_hours' => $bookedHours
        ]);
    }

    public function todayNextReservation()
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // Récupérer toutes les réservations d'aujourd'hui, non passées, triées par heure
        $reservations = Prenotazione::where('data', $today)
            ->where('payment_status', 'paid')
            ->whereRaw("STR_TO_DATE(CONCAT(data,' ',ora), '%Y-%m-%d %H:%i') >= ?", [$now])
            ->orderByRaw("STR_TO_DATE(CONCAT(data,' ',ora), '%Y-%m-%d %H:%i') ASC")
            ->get();

        // Garder uniquement la première réservation par utilisateur
        $result = $reservations->groupBy('user_id')->map(function($userReservations) {
            return $userReservations->first();
        })->values();

        return response()->json([
            'status' => true,
            'reservations' => $result
        ]);
    }
}
