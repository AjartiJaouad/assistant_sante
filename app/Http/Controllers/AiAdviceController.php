<?php

namespace App\Http\Controllers;

use App\Models\AiAdvice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class AiAdviceController extends Controller
{
    use ApiResponse;

    // US-06 & US-07 : Générer un conseil et le sauvegarder
    #[OA\Post(
        path: '/api/ai-advice',
        summary: 'Generer un conseil sante via IA Gemini',
        tags: ['Intelligence Artificielle'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Conseil genere et sauvegarde'),
            new OA\Response(response: 400, description: 'Aucun symptome trouve'),
            new OA\Response(response: 500, description: 'Erreur API Gemini'),
        ]
    )]
    public function generateAdvice(Request $request)
    {
        $user = $request->user();

        // Récupérer les 5 derniers symptômes de l'utilisateur
        $symptoms = $user->symptoms()->latest()->take(5)->get();

        if ($symptoms->isEmpty()) {
            return $this->error([], 'Vous n\'avez aucun symptôme enregistré à analyser.', 400);
        }

        // Préparer le prompt pour l'IA
        $symptomsList = $symptoms->pluck('name')->implode(', ');
        $prompt = "Tu es un assistant médical de premier niveau. L'utilisateur signale les symptômes suivants : {$symptomsList}. Donne un conseil général court (3 lignes maximum). Termine toujours en précisant que ce conseil ne remplace pas une vraie consultation médicale.";

        $apiKey = env('GEMINI_API_KEY');

        try {
            // Appel à l'API Gemini
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                // Extraire le texte de la réponse JSON de Gemini
                $adviceText = $response->json('candidates.0.content.parts.0.text');

                // US-07 : Sauvegarder le conseil dans la base de données
                $aiAdvice = AiAdvice::create([
                    'user_id' => $user->id,
                    'advice' => $adviceText,
                    'symptoms_analyzed' => $symptoms->pluck('name')->toArray(), // Sauvegardé en JSON
                    'generated_at' => now(),
                ]);

                return $this->success($aiAdvice, 'Conseil généré avec succès', 201);
            }

            return $this->error([], 'Échec de la connexion à l\'IA', 500);

        } catch (\Exception $e) {
            return $this->error([], 'Erreur technique : ' . $e->getMessage(), 500);
        }
    }

    // Afficher l'historique des conseils
    #[OA\Get(
        path: '/api/ai-advice',
        summary: 'Consulter l historique des conseils IA',
        tags: ['Intelligence Artificielle'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste de l historique des conseils'),
            new OA\Response(response: 401, description: 'Non autorise'),
        ]
    )]
    public function index(Request $request)
    {
        $advices = $request->user()->aiAdvices()->orderBy('generated_at', 'desc')->get();
        return $this->success($advices, 'Historique des conseils IA');
    }
}
