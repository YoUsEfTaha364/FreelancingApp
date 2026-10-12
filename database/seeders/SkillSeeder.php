<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $skills = [
            'PHP',
            'Laravel',
            'Python',
            'JavaScript',
            'React',
            'C#',
            '.NET',
            'Docker',
            'MySQL',
            'AI Automation',
            'Prompt Engineering',
            'AI Chatbots',
            'Figma',
            'Graphic Design',
            'Video Editing',
            'Adobe Premiere Pro',
            'After Effects',
            'Translation',
            'Content Writing',
            'Copywriting',
            'SEO',
            'Digital Marketing',
            'Social Media Management',
            'Data Analysis',
            'Microsoft Excel',
            'Power BI',
            'Virtual Assistant',
            'Data Entry',
            'Online Tutoring',
            'Career Coaching',
            'Resume Review',
            'Interview Preparation',
            'Research',
            'Presentation Creation',
            'Training Material',
            'Technical Support',
            'IT Support',
            'Software Testing',
            'QA Testing',
            'Manual Testing',
            'Automation Testing',
            'Selenium',
            'Postman',
            'API Testing',
        ];

        foreach ($skills as $skill) {
            Skill::updateOrCreate([
                'name' => $skill,
            ]);
        }
    }
}
