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
            ->where('payment_status', 'paid') 
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

    $next = Prenotazione::with('campo')  
        ->where('data', $today)
        ->where('payment_status', 'paid')
        ->whereRaw("STR_TO_DATE(CONCAT(data,' ',ora), '%Y-%m-%d %H:%i') >= ?", [$now])
        ->orderByRaw("STR_TO_DATE(CONCAT(data,' ',ora), '%Y-%m-%d %H:%i') ASC")
        ->first();

    return response()->json([
        'status' => true,
        'reservation' => $next
    ]);
}

public function getUserReservations(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer',
    ]);

    $reservations = Prenotazione::with('campo')
        ->where('user_id', $request->user_id)
        ->orderBy('data', 'asc')
        ->orderBy('ora', 'asc')
        ->get();

    return response()->json([
        'status' => true,
        'reservations' => $reservations
    ]);
}


}
