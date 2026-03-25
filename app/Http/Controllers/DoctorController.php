<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class DoctorController extends Controller
{
    use ApiResponse;

    // Lister tous les médecins
    public function index()
    {
        $doctors = Doctor::all();
        return $this->successResponse($doctors, 'Liste des médecins récupérée');
    }

    // Rechercher un médecin par spécialité ou ville
    public function search(Request $request)
    {
        $query = Doctor::query();

        if ($request->has('specialty')) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $doctors = $query->get();

        return $this->successResponse($doctors, 'Résultats de la recherche');
    }

    // Détail d'un médecin
    public function show($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return $this->errorResponse([], 'Médecin introuvable', 404);
        }

        return $this->successResponse($doctor, 'Détail du médecin');
    }
}
