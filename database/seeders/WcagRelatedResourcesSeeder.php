<?php

namespace Database\Seeders;

use App\Models\WcagRelatedResource;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Seeder;

class WcagRelatedResourcesSeeder extends Seeder
{
    public function run(): void
    {
        $criteria_111 = WcagSuccessCriterion::where('number', '1.1.1')->first();
        $criteria_143 = WcagSuccessCriterion::where('number', '1.4.3')->first();

        if ($criteria_111) {
            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_111->id,
                'type' => 'wai_failure',
                'code' => 'F3',
                'title_en' => 'Failure of Success Criterion 1.1.1 due to using CSS to include images',
                'title_sv' => 'Misslyckande av framgångskriterium 1.1.1 på grund av att CSS användes för att inkludera bilder',
                'description_en' => 'Using CSS background-image to display images makes them invisible to screen readers.',
                'description_sv' => 'Att använda CSS bakgrundsbild för att visa bilder gör dem osynliga för skärmläsare.',
                'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F3',
                'order' => 1,
            ]);

            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_111->id,
                'type' => 'wai_failure',
                'code' => 'F13',
                'title_en' => 'Failure of Success Criterion 1.1.1 due to having a text alternative',
                'title_sv' => 'Misslyckande på grund av saknad textalternativ',
                'description_en' => 'Images without alt attributes or alt text fail the criterion.',
                'description_sv' => 'Bilder utan alt-attribut eller alt-text uppfyller inte kriteriet.',
                'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F13',
                'order' => 2,
            ]);

            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_111->id,
                'type' => 'resource_link',
                'title_en' => 'W3C: Understanding Success Criterion 1.1.1',
                'title_sv' => 'W3C: Förståelse av framgångskriterium 1.1.1',
                'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
                'order' => 3,
            ]);

            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_111->id,
                'type' => 'resource_link',
                'title_en' => 'DIGG: Web Guidelines - Alt Text',
                'title_sv' => 'DIGG: Webbriktlinjer - Alt-text',
                'url' => 'https://webbriktlinjer.se/r/alternativ-text/',
                'order' => 4,
            ]);
        }

        if ($criteria_143) {
            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_143->id,
                'type' => 'wai_failure',
                'code' => 'F24',
                'title_en' => 'Failure of Success Criterion 1.4.3 due to using background images',
                'title_sv' => 'Misslyckande på grund av otillräcklig färgkontrast',
                'description_en' => 'Using background images without sufficient contrast fails the criterion.',
                'description_sv' => 'Bakgrundsbilder utan tillräcklig kontrast uppfyller inte kriteriet.',
                'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F24',
                'order' => 1,
            ]);

            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_143->id,
                'type' => 'resource_link',
                'title_en' => 'W3C: Understanding Success Criterion 1.4.3',
                'title_sv' => 'W3C: Förståelse av framgångskriterium 1.4.3',
                'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum',
                'order' => 2,
            ]);

            WcagRelatedResource::create([
                'wcag_success_criterion_id' => $criteria_143->id,
                'type' => 'resource_link',
                'title_en' => 'DIGG: Web Guidelines - Color Contrast',
                'title_sv' => 'DIGG: Webbriktlinjer - Färgkontrast',
                'url' => 'https://webbriktlinjer.se/r/forgrund-bakgrundsfarg-kontrast/',
                'order' => 3,
            ]);
        }
    }
}
