<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AppointmentController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/appointments',
        summary: 'Lister les rendez-vous de l utilisateur',
        tags: ['Rendez-vous'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des rendez-vous'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $appointments = $request->user()
            ->appointments()
            ->with('doctor')
            ->orderBy('appointment_date')
            ->get();

        return $this->successResponse($appointments, 'Liste de vos rendez-vous');
    }

    #[OA\Post(
        path: '/api/appointments',
        summary: 'Creer un rendez-vous',
        tags: ['Rendez-vous'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Rendez-vous cree'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $request->user()->appointments()->create([
            ...$request->validated(),
            'status' => 'pending',
        ]);

        return $this->successResponse($appointment->load('doctor'), 'Rendez-vous réservé avec succès', 201);
    }

    #[OA\Get(
        path: '/api/appointments/{id}',
        summary: 'Afficher un rendez-vous',
        tags: ['Rendez-vous'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Detail du rendez-vous'),
            new OA\Response(response: 404, description: 'Rendez-vous introuvable'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $appointment = $this->findUserAppointment($request, $id);

        return $this->successResponse($appointment, 'Détail du rendez-vous récupéré');
    }

    #[OA\Put(
        path: '/api/appointments/{id}',
        summary: 'Modifier un rendez-vous',
        tags: ['Rendez-vous'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Rendez-vous mis a jour'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateAppointmentRequest $request, int $id): JsonResponse
    {
        $appointment = $this->findUserAppointment($request, $id);
        $appointment->update($request->validated());

        return $this->successResponse($appointment->fresh()->load('doctor'), 'Rendez-vous mis à jour avec succès');
    }

    #[OA\Delete(
        path: '/api/appointments/{id}',
        summary: 'Supprimer un rendez-vous',
        tags: ['Rendez-vous'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Rendez-vous supprime'),
            new OA\Response(response: 404, description: 'Rendez-vous introuvable'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $appointment = $this->findUserAppointment($request, $id);
        $appointment->delete();

        return $this->successResponse([], 'Rendez-vous annulé avec succès');
    }

    private function findUserAppointment(Request $request, int $id): Appointment
    {
        return $request->user()->appointments()->with('doctor')->findOrFail($id);
    }
}
