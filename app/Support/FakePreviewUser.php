<?php

namespace App\Support;

use App\Models\User;

class FakePreviewUser
{
    /**
     * Build an example user (never persisted) used to render public
     * template previews on the welcome page.
     */
    public static function make(): User
    {
        $user = new User([
            'name' => 'Emma Lauridsen',
            'email' => 'emma.lauridsen@example.com',
            'job_title' => 'Senior Frontend Developer',
            'address' => 'Vesterbrogade 42',
            'zip' => '1620',
            'city' => 'København V',
            'phone' => '+45 28 34 56 78',
            'birthdate' => '1994-03-18',
            'skills' => [
                'JavaScript',
                'TypeScript',
                'Vue.js',
                'React',
                'Tailwind CSS',
                'Node.js',
                'Figma',
                'Git',
                'Agile / Scrum',
            ],
        ]);

        $user->avatar = self::avatar();
        $user->cv_json = [
            [
                'title' => 'Senior Frontend Developer',
                'company' => 'Nordic Tech ApS',
                'startDate' => '2021-08-01',
                'endDate' => null,
                'description' => 'Leading the frontend team building a design system used across four products. Improved Lighthouse scores from 62 to 96 and cut time-to-first-byte by 40%.',
            ],
            [
                'title' => 'Frontend Developer',
                'company' => 'Webbureau København',
                'startDate' => '2018-02-01',
                'endDate' => '2021-07-31',
                'description' => 'Built responsive marketing sites and web apps for 20+ clients. Introduced component-driven development with Vue.js and Storybook.',
            ],
            [
                'title' => 'Webudvikler (studiejob)',
                'company' => 'Pixel Studio',
                'startDate' => '2016-09-01',
                'endDate' => '2018-01-31',
                'description' => 'Developed and maintained WordPress themes and custom JavaScript integrations while studying.',
            ],
        ];

        return $user;
    }

    /**
     * A neutral portrait as an inline SVG data URI, so previews show a
     * photo without depending on any external service.
     */
    private static function avatar(): string
    {
        $svg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><rect width="200" height="200" fill="#e8ecf1"/><circle cx="100" cy="78" r="36" fill="#a3aebd"/><path d="M28 200c0-38 32-58 72-58s72 20 72 58Z" fill="#a3aebd"/></svg>
            SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
