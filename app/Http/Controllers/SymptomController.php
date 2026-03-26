<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSymptomRequest;
use App\Traits\ApiResponse;

class SymptomController extends Controller
{
    use ApiResponse;
/**
     * @OA\Get(
     * path="/api/symptoms",
     * summary="Lister les symptômes de l'utilisateur",
     * tags={"Symptômes"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Liste des symptômes"),
     * @OA\Response(response=401, description="Non autorisé")
     * )
     */
    // Lister les symptômes de l'utilisateur connecté
    public function index(Request $request)
    {
        $symptoms = $request->user()->symptoms()->orderBy('date_recorded', 'desc')->get();
        return $this->successResponse($symptoms, 'Symptômes récupérés avec succès');
    }
    /**
     * @OA\Post(
     * path="/api/symptoms",
     * summary="Enregistrer un nouveau symptôme",
     * tags={"Symptômes"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "severity"},
     * @OA\Property(property="name", type="string", example="Maux de tête"),
     * @OA\Property(property="severity", type="string", enum={"mild", "moderate", "severe"}),
     * @OA\Property(property="description", type="string", example="Douleur persistante"),
     * @OA\Property(property="date_recorded", type="string", format="date", example="2026-03-26")
     * )
     * ),
     * @OA\Response(response=201, description="Symptôme enregistré")
     * )
     */

    // Ajouter un symptôme
    public function store(StoreSymptomRequest $request)
    {
        $symptom = $request->user()->symptoms()->create($request->validated());
        return $this->successResponse($symptom, 'Symptôme enregistré avec succès', 201);
    }

    // Détail d'un symptôme
    public function show(Request $request, $id)
    {
        $symptom = $request->user()->symptoms()->find($id);

        if (!$symptom) {
            return $this->errorResponse([], 'Symptôme introuvable', 404);
        }

        return $this->successResponse($symptom, 'Détail du symptôme récupéré');
    }

    // Modifier un symptôme
    public function update(StoreSymptomRequest $request, $id)
    {
        $symptom = $request->user()->symptoms()->find($id);

        if (!$symptom) {
            return $this->errorResponse([], 'Symptôme introuvable', 404);
        }

        $symptom->update($request->validated());
        return $this->successResponse($symptom, 'Symptôme mis à jour avec succès');
    }

    // Supprimer un symptôme
    public function destroy(Request $request, $id)
    {
        $symptom = $request->user()->symptoms()->find($id);

        if (!$symptom) {
            return $this->errorResponse([], 'Symptôme introuvable', 404);
        }

        $symptom->delete();
        return $this->successResponse([], 'Symptôme supprimé avec succès');
    }
}
