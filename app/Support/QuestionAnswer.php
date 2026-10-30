<?php

namespace App\Support;

class QuestionAnswer
{
    /**
     * Normalize an answer value to the matching choice letter.
     *
     * @param array<string, string> $choices
     */
    public static function normalizeKey(string $answer, array $choices): string
    {
        $answer = trim($answer);
        $key = strtoupper($answer);

        if (in_array($key, ['A', 'B', 'C', 'D'], true)) {
            return $key;
        }

        foreach ($choices as $letter => $choice) {
            if (strtolower(trim($answer)) === strtolower(trim($choice))) {
                return $letter;
            }
        }

        return $key;
    }
}
