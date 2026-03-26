<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSymptomRequest;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class SymptomController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/symptoms',
        summary: 'Lister les symptomes de l utilisateur',
        tags: ['Symptomes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des symptomes'),
            new OA\Response(response: 401, description: 'Non autorise'),
        ]
    )]
    // Lister les symptômes de l'utilisateur connecté
    public function index(Request $request)
    {
        $symptoms = $request->user()->symptoms()->orderBy('date_recorded', 'desc')->get();
        return $this->success($symptoms, 'Symptômes récupérés avec succès');
    }

    #[OA\Post(
        path: '/api/symptoms',
        summary: 'Enregistrer un nouveau symptome',
        tags: ['Symptomes'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'severity'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maux de tete'),
                    new OA\Property(property: 'severity', type: 'string', enum: ['mild', 'moderate', 'severe']),
                    new OA\Property(property: 'description', type: 'string', example: 'Douleur persistante'),
                    new OA\Property(property: 'date_recorded', type: 'string', format: 'date', example: '2026-03-26'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Symptome enregistre'),
        ]
    )]

    // Ajouter un symptôme
    public function store(StoreSymptomRequest $request)
    {
        $symptom = $request->user()->symptoms()->create($request->validated());
        return $this->success($symptom, 'Symptôme enregistré avec succès', 201);
    }

    // Détail d'un symptôme
    public function show(Request $request, $id)
    {
        $symptom = $request->user()->symptoms()->find($id);

        if (!$symptom) {
            return $this->error([], 'Symptôme introuvable', 404);
        }

        return $this->success($symptom, 'Détail du symptôme récupéré');
    }

    // Modifier un symptôme
    public function update(StoreSymptomRequest $request, $id)
    {
        $symptom = $request->user()->symptoms()->find($id);

        if (!$symptom) {
            return $this->error([], 'Symptôme introuvable', 404);
        }

        $symptom->update($request->validated());
        return $this->success($symptom, 'Symptôme mis à jour avec succès');
    }

    // Supprimer un symptôme
    public function destroy(Request $request, $id)
    {
        $symptom = $request->user()->symptoms()->find($id);

        if (!$symptom) {
            return $this->error([], 'Symptôme introuvable', 404);
        }

        $symptom->delete();
        return $this->success([], 'Symptôme supprimé avec succès');
    }
}
