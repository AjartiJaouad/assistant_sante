<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAppointmentRequest;
use App\Traits\ApiResponse;

class AppointmentController extends Controller
{
    use ApiResponse;

    // US-05 : Lister les rendez-vous de l'utilisateur connecté
    public function index(Request $request)
    {
        // On inclut les infos du médecin avec "with('doctor')"
        $appointments = $request->user()->appointments()->with('doctor')->orderBy('appointment_date', 'asc')->get();
        return $this->successResponse($appointments, 'Liste de vos rendez-vous');
    }

    // US-04 : Prendre un rendez-vous
    public function store(StoreAppointmentRequest $request)
    {
        $appointment = $request->user()->appointments()->create([
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'notes' => $request->notes,
            'status' => 'pending', // Statut par défaut
        ]);

        return $this->successResponse($appointment, 'Rendez-vous réservé avec succès', 201);
    }

    // Annuler/Supprimer un rendez-vous
    public function destroy(Request $request, $id)
    {
        $appointment = $request->user()->appointments()->find($id);

        if (!$appointment) {
            return $this->errorResponse([], 'Rendez-vous introuvable ou non autorisé', 404);
        }

        $appointment->delete();
        return $this->successResponse([], 'Rendez-vous annulé avec succès');
    }
}
