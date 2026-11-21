<?php

namespace App\Http\Controllers;

use App\Models\TriageRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;

class TriageController extends Controller
{
    private const SEVERITIES = ['low', 'medium', 'emergency'];

    private const NEXT_STEPS = ['self_care', 'see_doctor', 'urgent_care', 'emergency'];

    private const LIKELIHOODS = ['high', 'medium', 'low'];

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are TriageIQ, an AI medical triage assistant. You assess urgency; you do not give a definitive diagnosis.
Respond with a single JSON object and nothing else, with exactly these fields:
- "severity": one of "low", "medium", "emergency"
- "next_step": one of "self_care", "see_doctor", "urgent_care", "emergency"
    self_care   = can be managed at home
    see_doctor  = book a GP or clinic visit within a few days
    urgent_care = should be seen by a clinician within 24 hours
    emergency   = call emergency services or go to the emergency department now
- "advice": what the patient should do next, 1-3 plain-language sentences
- "reason": why you chose this severity, 1-3 sentences
- "possible_conditions": up to 4 objects {"name": string, "likelihood": "high" | "medium" | "low"}; possibilities to discuss with a clinician, not a diagnosis
- "red_flags": up to 5 short strings; symptoms that mean the patient must seek emergency care immediately
- "self_care": up to 5 short, safe self-care tips (an empty array if severity is "emergency")
- "confidence": integer 0-100, how confident you are in the severity given the information provided
If the symptoms could indicate a life-threatening condition, use "emergency". When unsure between two levels, choose the more urgent one.
Use the patient's age, sex, symptom duration and existing conditions when provided.
PROMPT;

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'symptoms' => 'required|string|max:2000',
            'age' => 'nullable|integer|min:0|max:120',
            'sex' => 'nullable|in:female,male,other',
            'duration' => 'nullable|string|max:50',
            'conditions' => 'nullable|string|max:500',
        ]);

        $symptoms = $validated['symptoms'];
        $patient = array_filter([
            'age' => $validated['age'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'conditions' => $validated['conditions'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $apiKey = config('services.groq.key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'GROQ_API_KEY is not configured.'], 500);
        }

        try {
            $client = OpenAI::factory()
                ->withApiKey($apiKey)
                ->withBaseUri(config('services.groq.base_uri'))
                ->make();

            $response = $client->chat()->create([
                'model' => config('services.groq.model'),
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $this->buildUserMessage($symptoms, $patient)],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Groq triage request failed', ['exception' => $e]);

            return response()->json([
                'error' => 'The AI service is unavailable. Please try again.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 502);
        }

        $clean = trim(preg_replace('/```(?:json)?|```/', '', $response->choices[0]->message->content ?? ''));
        $decoded = json_decode($clean, true);
        $ai = $this->normalize(is_array($decoded) ? $decoded : []);

        $record = TriageRecord::create([
            'user_id' => $request->user()?->id,
            'symptoms' => $symptoms,
            'patient' => $patient ?: null,
            'severity' => $ai['severity'],
            'advice' => $ai['advice'],
            'reason' => $ai['reason'],
            'details' => array_diff_key($ai, array_flip(['severity', 'advice', 'reason'])),
            'analysis' => $clean,
        ]);

        return response()->json([
            'record' => $this->present($record),
            'ai' => $ai,
        ]);
    }

    public function history()
    {
        $counts = TriageRecord::query()
            ->selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $records = TriageRecord::latest()->latest('id')->limit(50)->get();

        return response()->json([
            'stats' => [
                'total' => (int) $counts->sum(),
                'emergency' => (int) ($counts['emergency'] ?? 0),
                'medium' => (int) ($counts['medium'] ?? 0),
                'low' => (int) ($counts['low'] ?? 0),
            ],
            'records' => $records->map(fn (TriageRecord $r) => $this->present($r))->values(),
        ]);
    }

    private function buildUserMessage(string $symptoms, array $patient): string
    {
        $lines = ["Symptoms: {$symptoms}"];
        $labels = ['age' => 'Age', 'sex' => 'Sex', 'duration' => 'Duration', 'conditions' => 'Existing conditions'];
        foreach ($labels as $key => $label) {
            if (isset($patient[$key])) {
                $lines[] = "{$label}: {$patient[$key]}";
            }
        }

        return implode("\n", $lines);
    }

    private function normalize(array $ai): array
    {
        $severity = strtolower(trim((string) ($ai['severity'] ?? '')));
        $severity = in_array($severity, self::SEVERITIES, true) ? $severity : 'unknown';

        $nextStep = strtolower(trim((string) ($ai['next_step'] ?? '')));
        if ($severity === 'emergency') {
            $nextStep = 'emergency';
        } elseif (! in_array($nextStep, self::NEXT_STEPS, true)) {
            $nextStep = $severity === 'low' ? 'self_care' : 'see_doctor';
        }

        $conditions = [];
        foreach ((array) ($ai['possible_conditions'] ?? []) as $c) {
            $name = is_array($c) ? trim((string) ($c['name'] ?? '')) : (is_scalar($c) ? trim((string) $c) : '');
            if ($name === '') {
                continue;
            }
            $likelihood = is_array($c) ? strtolower((string) ($c['likelihood'] ?? '')) : '';
            $conditions[] = [
                'name' => $name,
                'likelihood' => in_array($likelihood, self::LIKELIHOODS, true) ? $likelihood : 'medium',
            ];
        }

        $confidence = $ai['confidence'] ?? null;
        $confidence = is_numeric($confidence) ? max(0, min(100, (int) round($confidence))) : null;

        return [
            'severity' => $severity,
            'next_step' => $nextStep,
            'advice' => trim((string) ($ai['advice'] ?? '')),
            'reason' => trim((string) ($ai['reason'] ?? '')),
            'possible_conditions' => array_slice($conditions, 0, 4),
            'red_flags' => $this->stringList($ai['red_flags'] ?? [], 5),
            'self_care' => $severity === 'emergency' ? [] : $this->stringList($ai['self_care'] ?? [], 5),
            'confidence' => $confidence,
        ];
    }

    private function stringList(mixed $value, int $max): array
    {
        if (! is_array($value)) {
            return [];
        }
        $items = array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $value);

        return array_slice(array_values(array_filter($items, fn ($v) => $v !== '')), 0, $max);
    }

    private function present(TriageRecord $r): array
    {
        return [
            'id' => $r->id,
            'symptoms' => $r->symptoms,
            'patient' => $r->patient ?? (object) [],
            'created_at' => $r->created_at?->toIso8601String(),
            'ai' => array_merge(
                ['next_step' => null, 'possible_conditions' => [], 'red_flags' => [], 'self_care' => [], 'confidence' => null],
                $r->details ?? [],
                ['severity' => $r->severity, 'advice' => $r->advice ?? '', 'reason' => $r->reason ?? ''],
            ),
        ];
    }
}
