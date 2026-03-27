<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DoctorController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/doctors',
        summary: 'Lister les medecins',
        tags: ['Medecins'],
        responses: [
            new OA\Response(response: 200, description: 'Liste des medecins'),
        ]
    )]
    public function index(): JsonResponse
    {
        $doctors = Doctor::query()->orderBy('name')->get();

        return $this->successResponse($doctors, 'Liste des médecins récupérée');
    }

    #[OA\Get(
        path: '/api/doctors/search',
        summary: 'Rechercher un medecin par specialite ou ville',
        tags: ['Medecins'],
        responses: [
            new OA\Response(response: 200, description: 'Resultats de recherche'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $query = Doctor::query();

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%'.$request->string('specialty').'%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city').'%');
        }

        return $this->successResponse($query->orderBy('name')->get(), 'Résultats de la recherche');
    }

    #[OA\Get(
        path: '/api/doctors/{id}',
        summary: 'Afficher le detail d un medecin',
        tags: ['Medecins'],
        responses: [
            new OA\Response(response: 200, description: 'Detail du medecin'),
            new OA\Response(response: 404, description: 'Medecin introuvable'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $doctor = Doctor::find($id);

        if (! $doctor) {
            return $this->errorResponse([], 'Médecin introuvable', 404);
        }

        return $this->successResponse($doctor, 'Détail du médecin');
    }
}
