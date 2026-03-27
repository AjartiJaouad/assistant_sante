<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSymptomRequest;
use App\Http\Requests\UpdateSymptomRequest;
use App\Models\Symptom;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): JsonResponse
    {
        $symptoms = $request->user()->symptoms()->orderByDesc('date_recorded')->orderByDesc('id')->get();

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
                required: ['name', 'severity', 'date_recorded'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maux de tete'),
                    new OA\Property(property: 'severity', type: 'string', enum: ['mild', 'moderate', 'severe']),
                    new OA\Property(property: 'description', type: 'string', example: 'Douleur persistante'),
                    new OA\Property(property: 'date_recorded', type: 'string', format: 'date', example: '2026-03-26'),
                    new OA\Property(property: 'notes', type: 'string', example: 'Hydratation faible'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Symptome enregistre'),
        ]
    )]
    public function store(StoreSymptomRequest $request): JsonResponse
    {
        $symptom = $request->user()->symptoms()->create($request->validated());

        return $this->success($symptom, 'Symptôme enregistré avec succès', 201);
    }

    #[OA\Get(
        path: '/api/symptoms/{id}',
        summary: 'Afficher un symptome',
        tags: ['Symptomes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Detail du symptome'),
            new OA\Response(response: 404, description: 'Symptome introuvable'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $symptom = $this->findUserSymptom($request, $id);

        return $this->success($symptom, 'Détail du symptôme récupéré');
    }

    #[OA\Put(
        path: '/api/symptoms/{id}',
        summary: 'Modifier un symptome',
        tags: ['Symptomes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Symptome mis a jour'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateSymptomRequest $request, int $id): JsonResponse
    {
        $symptom = $this->findUserSymptom($request, $id);
        $symptom->update($request->validated());

        return $this->success($symptom->fresh(), 'Symptôme mis à jour avec succès');
    }

    #[OA\Delete(
        path: '/api/symptoms/{id}',
        summary: 'Supprimer un symptome',
        tags: ['Symptomes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Symptome supprime'),
            new OA\Response(response: 404, description: 'Symptome introuvable'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $symptom = $this->findUserSymptom($request, $id);
        $symptom->delete();

        return $this->success([], 'Symptôme supprimé avec succès');
    }

    private function findUserSymptom(Request $request, int $id): Symptom
    {
        return $request->user()->symptoms()->findOrFail($id);
    }
}
