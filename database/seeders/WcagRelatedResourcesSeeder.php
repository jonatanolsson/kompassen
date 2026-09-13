<?php

namespace Database\Seeders;

use App\Models\WcagRelatedResource;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Seeder;

class WcagRelatedResourcesSeeder extends Seeder
{
    public function run(): void
    {
        WcagRelatedResource::whereIn('url', [
            'https://webbriktlinjer.se/r/alternativ-text/',
            'https://webbriktlinjer.se/r/forgrund-bakgrundsfarg-kontrast/',
        ])->delete();

        $addResource = function (string $criterionNumber, array $resource): void {
            $criterion = WcagSuccessCriterion::where('number', $criterionNumber)->first();

            if (! $criterion) {
                return;
            }

            $identity = [
                'wcag_success_criterion_id' => $criterion->id,
                'type' => $resource['type'],
            ];

            if ($resource['code'] ?? null) {
                $identity['code'] = $resource['code'];
            } else {
                $identity['title_en'] = $resource['title_en'];
            }

            WcagRelatedResource::updateOrCreate($identity, [
                ...$resource,
                'wcag_success_criterion_id' => $criterion->id,
            ]);
        };

        $addResource('1.1.1', [
            'type' => 'wai_failure',
            'code' => 'F3',
            'title_en' => 'Failure of Success Criterion 1.1.1 due to using CSS to include images',
            'title_sv' => 'Misslyckande av framgångskriterium 1.1.1 på grund av att CSS användes för att inkludera bilder',
            'description_en' => 'Using CSS background-image to display images makes them invisible to screen readers.',
            'description_sv' => 'Att använda CSS bakgrundsbild för att visa bilder gör dem osynliga för skärmläsare.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F3',
            'order' => 1,
        ]);

        $addResource('1.1.1', [
            'type' => 'wai_failure',
            'code' => 'F13',
            'title_en' => 'Failure of Success Criterion 1.1.1 due to missing a text alternative',
            'title_sv' => 'Misslyckande på grund av saknad textalternativ',
            'description_en' => 'Images without alt attributes or alt text fail the criterion.',
            'description_sv' => 'Bilder utan alt-attribut eller alt-text uppfyller inte kriteriet.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F13',
            'order' => 2,
        ]);

        $addResource('1.1.1', [
            'type' => 'resource_link',
            'title_en' => 'W3C: Understanding Success Criterion 1.1.1',
            'title_sv' => 'W3C: Förståelse av framgångskriterium 1.1.1',
            'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-content',
            'order' => 3,
        ]);

        $addResource('1.1.1', [
            'type' => 'resource_link',
            'title_en' => 'DIGG: Web Guidelines - Alt Text',
            'title_sv' => 'DIGG: Webbriktlinjer - Alt-text',
            'url' => 'https://www.digg.se/webbriktlinjer/alla-webbriktlinjer/beskriv-med-text-allt-innehall-som-inte-ar-text',
            'order' => 4,
        ]);

        $addResource('1.4.3', [
            'type' => 'wai_failure',
            'code' => 'F24',
            'title_en' => 'Failure of Success Criterion 1.4.3 due to insufficient contrast',
            'title_sv' => 'Misslyckande på grund av otillräcklig färgkontrast',
            'description_en' => 'Background images without sufficient contrast can make text unreadable.',
            'description_sv' => 'Bakgrundsbilder utan tillräcklig kontrast kan göra text svårläst.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F24',
            'order' => 1,
        ]);

        $addResource('1.4.3', [
            'type' => 'resource_link',
            'title_en' => 'W3C: Understanding Success Criterion 1.4.3',
            'title_sv' => 'W3C: Förståelse av framgångskriterium 1.4.3',
            'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum',
            'order' => 2,
        ]);

        $addResource('1.4.3', [
            'type' => 'resource_link',
            'title_en' => 'DIGG: Web Guidelines - Text and Background Contrast',
            'title_sv' => 'DIGG: Webbriktlinjer - Kontrast mellan text och bakgrund',
            'url' => 'https://www.digg.se/webbriktlinjer/alla-webbriktlinjer/anvand-tillracklig-kontrast-mellan-text-och-bakgrund',
            'order' => 3,
        ]);

        $addResource('2.1.1', [
            'type' => 'resource_link',
            'title_en' => 'DIGG: Web Guidelines - Keyboard Accessibility',
            'title_sv' => 'DIGG: Webbriktlinjer - All funktionalitet ska kunna användas med tangentbord',
            'url' => 'https://www.digg.se/webbriktlinjer/alla-webbriktlinjer/all-funktionalitet-ska-kunna-anvandas-med-tangentbord',
            'order' => 1,
        ]);
    }
}
