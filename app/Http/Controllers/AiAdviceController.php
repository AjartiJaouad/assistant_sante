<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateAiAdviceRequest;
use App\Models\AiAdvice;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Attributes as OA;

class AiAdviceController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: '/api/ai/health-advice',
        summary: 'Generer un conseil sante via IA Gemini',
        tags: ['Intelligence Artificielle'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Conseil genere et sauvegarde'),
            new OA\Response(response: 400, description: 'Aucun symptome trouve'),
            new OA\Response(response: 500, description: 'Erreur API Gemini'),
        ]
    )]
    public function generateAdvice(GenerateAiAdviceRequest $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->validated('limit') ?? 5;
        $symptoms = $user->symptoms()
            ->orderByDesc('date_recorded')
            ->orderByDesc('id')
            ->take($limit)
            ->get();

        if ($symptoms->isEmpty()) {
            return $this->error([], 'Vous n\'avez aucun symptôme enregistré à analyser.', 400);
        }

        $symptomLines = $symptoms
            ->map(fn ($symptom) => sprintf(
                '- %s (gravite: %s%s)',
                $symptom->name,
                $symptom->severity,
                $symptom->description ? ', description: '.$symptom->description : ''
            ))
            ->implode("\n");

        $prompt = <<<PROMPT
User symptoms:
{$symptomLines}

Provide general wellness advice only.
Do not provide a medical diagnosis.
Keep the answer concise and remind the user to consult a doctor if symptoms persist or worsen.
PROMPT;

        $apiKey = (string) config('services.gemini.api_key');

        if ($apiKey === '') {
            return $this->error([], 'La clé API Gemini est absente.', 500);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(20)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                ]
            );

            if (! $response->successful()) {
                return $this->error([
                    'provider' => [$response->json('error.message') ?: 'La requête vers Gemini a échoué.'],
                ], 'Échec de la connexion à l\'IA', 502);
            }

            $adviceText = trim((string) $response->json('candidates.0.content.parts.0.text'));

            if ($adviceText === '') {
                return $this->error([], 'Réponse IA invalide.', 502);
            }

            $aiAdvice = AiAdvice::create([
                'user_id' => $user->id,
                'advice' => $adviceText,
                'symptoms_analyzed' => $symptoms->map(fn ($symptom) => [
                    'id' => $symptom->id,
                    'name' => $symptom->name,
                    'severity' => $symptom->severity,
                    'date_recorded' => optional($symptom->date_recorded)->toDateString(),
                ])->values()->all(),
                'generated_at' => now(),
            ]);

            return $this->success([
                'id' => $aiAdvice->id,
                'advice' => $aiAdvice->advice,
                'generated_at' => $aiAdvice->generated_at,
                'symptoms_analyzed' => $aiAdvice->symptoms_analyzed,
            ], 'Conseil généré avec succès', 201);
        } catch (\Throwable $exception) {
            return $this->error([], 'Erreur technique : '.$exception->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/ai/health-advice/history',
        summary: 'Consulter l historique des conseils IA',
        tags: ['Intelligence Artificielle'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste de l historique des conseils'),
            new OA\Response(response: 401, description: 'Non autorise'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $advices = $request->user()->aiAdvices()->orderByDesc('generated_at')->get();

        return $this->success($advices, 'Historique des conseils IA');
    }
}
