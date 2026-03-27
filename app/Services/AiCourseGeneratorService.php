<?php

namespace App\Services;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Enums\Lab;
use function Laravel\Ai\agent;

class AiCourseGeneratorService
{
    /**
     * @param string $categoryName
     * @return array
     */
    public function generateCourses(string $categoryName): array
    {
        $response = agent(
            instructions: 'You are an educational assistant helping to map out popular courses. Support generating in Arabic or English depending on the topic. Provide the output strictly as a JSON array of strings.',
        )->prompt(
            "Generate a list of 10 to 20 popular concise course titles related to the educational category: '{$categoryName}'. Provide the output as a JSON array of strings ONLY. Do not wrap in markdown.",
            provider: Lab::Gemini
        );

        $jsonString = trim((string) $response);
        // Sometimes LLMs wrap JSON in markdown blocks
        if (str_starts_with($jsonString, '```json')) {
            $jsonString = substr($jsonString, 7);
            $jsonString = substr($jsonString, 0, -3);
            $jsonString = trim($jsonString);
        } elseif (str_starts_with($jsonString, '```')) {
            $jsonString = substr($jsonString, 3);
            $jsonString = substr($jsonString, 0, -3);
            $jsonString = trim($jsonString);
        }

        $data = json_decode($jsonString, true);

        return is_array($data) ? $data : [];
    }
}
