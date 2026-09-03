<?php

namespace Database\Seeders;

use App\Models\TestingMethodology;
use Illuminate\Database\Seeder;

class TestingMethodologySeeder extends Seeder
{
    public function run(): void
    {
        $methodologies = [
            // Screen readers
            ['name' => 'JAWS', 'category' => 'screen_reader', 'description' => 'Job Access With Speech – Windows screen reader by Freedom Scientific'],
            ['name' => 'NVDA', 'category' => 'screen_reader', 'description' => 'NonVisual Desktop Access – free open-source screen reader for Windows'],
            ['name' => 'VoiceOver (macOS)', 'category' => 'screen_reader', 'description' => 'Built-in screen reader in macOS'],
            ['name' => 'VoiceOver (iOS)', 'category' => 'screen_reader', 'description' => 'Built-in screen reader in iOS/iPadOS'],
            ['name' => 'TalkBack', 'category' => 'screen_reader', 'description' => 'Built-in screen reader for Android'],
            ['name' => 'Narrator', 'category' => 'screen_reader', 'description' => 'Built-in screen reader in Windows'],
            ['name' => 'Orca', 'category' => 'screen_reader', 'description' => 'Screen reader for Linux (GNOME)'],

            // Browsers
            ['name' => 'Google Chrome', 'category' => 'browser', 'description' => null],
            ['name' => 'Mozilla Firefox', 'category' => 'browser', 'description' => null],
            ['name' => 'Apple Safari', 'category' => 'browser', 'description' => null],
            ['name' => 'Microsoft Edge', 'category' => 'browser', 'description' => null],

            // Browser extensions
            ['name' => 'axe DevTools', 'category' => 'browser_extension', 'description' => 'Automated accessibility testing by Deque Systems'],
            ['name' => 'WAVE', 'category' => 'browser_extension', 'description' => 'Web Accessibility Evaluation Tool by WebAIM'],
            ['name' => 'Lighthouse', 'category' => 'browser_extension', 'description' => 'Built-in Chrome DevTools accessibility auditing'],
            ['name' => 'Accessibility Insights', 'category' => 'browser_extension', 'description' => 'Accessibility testing tool by Microsoft'],

            // Devices
            ['name' => 'Desktop – Windows', 'category' => 'device', 'description' => null],
            ['name' => 'Desktop – macOS', 'category' => 'device', 'description' => null],
            ['name' => 'Desktop – Linux', 'category' => 'device', 'description' => null],
            ['name' => 'iPhone', 'category' => 'device', 'description' => null],
            ['name' => 'iPad', 'category' => 'device', 'description' => null],
            ['name' => 'Android phone', 'category' => 'device', 'description' => null],
            ['name' => 'Android tablet', 'category' => 'device', 'description' => null],

            // Testing tools
            ['name' => 'Colour Contrast Analyser', 'category' => 'testing_tool', 'description' => 'Desktop tool for checking colour contrast ratios by TPGi'],
            ['name' => 'Keyboard-only navigation', 'category' => 'testing_tool', 'description' => 'Manual testing using only keyboard (Tab, Arrow keys, Enter, Escape)'],
            ['name' => 'Zoom (400%)', 'category' => 'testing_tool', 'description' => 'Browser zoom to 400% to verify reflow and readability'],
            ['name' => 'ARC Toolkit', 'category' => 'testing_tool', 'description' => 'Accessibility testing toolkit by TPGi'],
        ];

        foreach ($methodologies as $data) {
            TestingMethodology::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}