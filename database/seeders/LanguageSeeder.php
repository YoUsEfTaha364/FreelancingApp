<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            'English',
            'Arabic',
            'French',
            'Spanish',
            'German',
            'Italian',
            'Portuguese',
            'Russian',
            'Chinese',
            'Japanese',
            'Korean',
            'Turkish',
            'Dutch',
            'Swedish',
            'Norwegian',
            'Danish',
            'Finnish',
            'Polish',
            'Ukrainian',
            'Czech',
            'Greek',
            'Hebrew',
            'Hindi',
            'Urdu',
            'Bengali',
            'Persian',
            'Indonesian',
            'Malay',
            'Thai',
            'Vietnamese',
            'Romanian',
            'Hungarian',
            'Bulgarian',
            'Serbian',
            'Croatian',
            'Slovak',
            'Slovenian',
            'Lithuanian',
            'Latvian',
            'Estonian',
            'Swahili',
            'Amharic',
            'Afrikaans',
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate([
                'name' => $language,
            ]);
        }
    }
}