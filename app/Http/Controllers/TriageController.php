<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use App\Models\TriageRecord;

class TriageController extends Controller
{
    private function cleanJson($text)
    {
        return trim(preg_replace('/```(?:json)?|```/', '', $text));
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'symptoms' => 'required|string',
        ]);

        $symptoms = $request->symptoms;

        $system = "You are an AI medical triage assistant. 
        Return STRICT JSON ONLY with fields: severity, advice, reason.";

        $client = OpenAI::factory()
            ->withApiKey(env('GROQ_API_KEY'))
            ->withBaseUri('https://api.groq.com/openai/v1')
            ->make();

        $response = $client->chat()->create([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Symptoms: $symptoms"],
            ],
        ]);

        $raw = $response->choices[0]->message->content;
        $clean = $this->cleanJson($raw);
        $ai = json_decode($clean, true) ?? [];

        // Save to DB
        $record = TriageRecord::create([
            'symptoms' => $symptoms,
            'severity' => $ai['severity'] ?? 'unknown',
            'advice'   => $ai['advice'] ?? '',
            'reason'   => $ai['reason'] ?? '',
            'analysis' => $clean,
        ]);

        return response()->json([
            'analysis' => $clean,   // 👈 THIS FIXES YOUR UI
            'severity' => $ai['severity'] ?? 'unknown'
        ]);
    }
}
