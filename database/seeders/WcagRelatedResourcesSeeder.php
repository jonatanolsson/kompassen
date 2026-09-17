<?php

namespace Database\Seeders;

use App\Models\WcagRelatedResource;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

            $resourceModel = WcagRelatedResource::firstOrNew($identity);
            $resourceModel->fill([
                ...$resource,
                'wcag_success_criterion_id' => $criterion->id,
            ]);

            if (! $resourceModel->exists) {
                $resourceModel->id = Str::ulid();
            }

            $resourceModel->save();
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

        $addResource('2.2.1', [
            'type' => 'wai_failure',
            'code' => 'F41',
            'title_en' => 'Failure of Success Criterion 2.2.1 due to using meta refresh to reload the page',
            'title_sv' => 'Misslyckande på grund av att meta-refresh används för att ladda om sidan',
            'description_en' => 'If the time interval before an automatic refresh is too short and cannot be turned off, users may not have enough time to read or interact with the page.',
            'description_sv' => 'Om tidsintervallet innan en automatisk omladdning är för kort och inte kan stängas av kan användare sakna tillräckligt med tid att läsa eller interagera med sidan.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F41',
            'order' => 1,
        ]);

        $addResource('2.2.2', [
            'type' => 'wai_failure',
            'code' => 'F16',
            'title_en' => 'Failure of Success Criterion 2.2.2 due to including scrolling content without a mechanism to pause and restart it',
            'title_sv' => 'Misslyckande på grund av rullande innehåll utan mekanism för att pausa och starta om',
            'description_en' => 'Scrolling, marquee-style content that starts automatically and cannot be paused prevents some users from reading it.',
            'description_sv' => 'Rullande innehåll som startar automatiskt och inte kan pausas gör att vissa användare inte hinner läsa det.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F16',
            'order' => 1,
        ]);

        $addResource('2.5.1', [
            'type' => 'wai_failure',
            'code' => 'F105',
            'title_en' => 'Failure of Success Criterion 2.5.1 due to providing functionality via a path-based gesture without a simple pointer alternative',
            'title_sv' => 'Misslyckande på grund av att funktionalitet endast kan användas med en bangaserad gest',
            'description_en' => 'Requiring a swipe or drag gesture to perform an action, without a single-tap or click alternative, excludes users who cannot perform precise gestures.',
            'description_sv' => 'Att kräva en svep- eller dragrörelse för en åtgärd, utan alternativ med enkel tryckning eller klick, utestänger användare som inte kan utföra precisa gester.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F105',
            'order' => 1,
        ]);

        $addResource('2.5.3', [
            'type' => 'wai_failure',
            'code' => 'F96',
            'title_en' => 'Failure due to the accessible name not containing the visible label text',
            'title_sv' => 'Misslyckande på grund av att det tillgängliga namnet inte innehåller den synliga etikettexten',
            'description_en' => 'When the accessible name differs from the visible label, speech-input users cannot reliably activate the control by speaking the label they see.',
            'description_sv' => 'När det tillgängliga namnet skiljer sig från den synliga etiketten kan röststyrningsanvändare inte tillförlitligt aktivera kontrollen genom att säga etiketten de ser.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F96',
            'order' => 1,
        ]);

        $addResource('3.2.3', [
            'type' => 'wai_failure',
            'code' => 'F66',
            'title_en' => 'Failure of Success Criterion 3.2.3 due to presenting navigation links in a different relative order on different pages',
            'title_sv' => 'Misslyckande på grund av att navigeringslänkar visas i olika ordning på olika sidor',
            'description_en' => 'Repeated navigation mechanisms that change relative order between pages make it harder for users to predict where to find them.',
            'description_sv' => 'Upprepade navigeringsmekanismer som byter relativ ordning mellan sidor gör det svårare för användare att förutse var de finns.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F66',
            'order' => 1,
        ]);

        $addResource('4.1.1', [
            'type' => 'wai_failure',
            'code' => 'F70',
            'title_en' => 'Failure of Success Criterion 4.1.1 due to incorrect use of start and end tags or attribute markup',
            'title_sv' => 'Misslyckande på grund av felaktig användning av start- och sluttaggar eller attributmarkering',
            'description_en' => 'Markup errors in element tags can cause assistive technologies to misinterpret or fail to expose content correctly.',
            'description_sv' => 'Felaktig markering i elementtaggar kan göra att hjälpmedelsteknik feltolkar eller inte korrekt exponerar innehållet.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F70',
            'order' => 1,
        ]);

        $addResource('4.1.1', [
            'type' => 'wai_failure',
            'code' => 'F77',
            'title_en' => 'Failure of Success Criterion 4.1.1 due to duplicate values of type ID',
            'title_sv' => 'Misslyckande på grund av dubblerade värden av typen ID',
            'description_en' => 'Duplicate id attributes are known to cause problems for assistive technologies interacting with the affected elements.',
            'description_sv' => 'Dubblerade id-attribut är kända för att orsaka problem för hjälpmedelsteknik som interagerar med de berörda elementen.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F77',
            'order' => 2,
        ]);
    }
}
