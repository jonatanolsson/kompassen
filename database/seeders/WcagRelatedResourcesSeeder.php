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

        WcagRelatedResource::query()
            ->where('type', 'resource_link')
            ->whereHas('wcagSuccessCriterion', fn ($query) => $query->where('standard', 'WCAG 2.1'))
            ->whereIn('title_en', [
                'DIGG: Web Guidelines',
                'DIGG: Web Guidelines - Alt Text',
                'DIGG: Web Guidelines - Text and Background Contrast',
                'DIGG: Web Guidelines - Keyboard Accessibility',
            ])
            ->delete();

        $addResource = function (string $standard, string $criterionNumber, array $resource): void {
            $criterion = WcagSuccessCriterion::where('standard', $standard)
                ->where('number', $criterionNumber)
                ->first();

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

        $addResource('WCAG 2.1', '1.1.1', [
            'type' => 'wai_failure',
            'code' => 'F3',
            'title_en' => 'Failure of Success Criterion 1.1.1 due to using CSS to include images',
            'title_sv' => 'Misslyckande av framgångskriterium 1.1.1 på grund av att CSS användes för att inkludera bilder',
            'description_en' => 'Using CSS background-image to display images makes them invisible to screen readers.',
            'description_sv' => 'Att använda CSS bakgrundsbild för att visa bilder gör dem osynliga för skärmläsare.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F3',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '1.1.1', [
            'type' => 'wai_failure',
            'code' => 'F13',
            'title_en' => 'Failure of Success Criterion 1.1.1 due to missing a text alternative',
            'title_sv' => 'Misslyckande på grund av saknad textalternativ',
            'description_en' => 'Images without alt attributes or alt text fail the criterion.',
            'description_sv' => 'Bilder utan alt-attribut eller alt-text uppfyller inte kriteriet.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F13',
            'order' => 2,
        ]);

        $addResource('WCAG 2.1', '1.4.3', [
            'type' => 'wai_failure',
            'code' => 'F24',
            'title_en' => 'Failure of Success Criterion 1.4.3 due to insufficient contrast',
            'title_sv' => 'Misslyckande på grund av otillräcklig färgkontrast',
            'description_en' => 'Background images without sufficient contrast can make text unreadable.',
            'description_sv' => 'Bakgrundsbilder utan tillräcklig kontrast kan göra text svårläst.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F24',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '2.2.1', [
            'type' => 'wai_failure',
            'code' => 'F41',
            'title_en' => 'Failure of Success Criterion 2.2.1 due to using meta refresh to reload the page',
            'title_sv' => 'Misslyckande på grund av att meta-refresh används för att ladda om sidan',
            'description_en' => 'If the time interval before an automatic refresh is too short and cannot be turned off, users may not have enough time to read or interact with the page.',
            'description_sv' => 'Om tidsintervallet innan en automatisk omladdning är för kort och inte kan stängas av kan användare sakna tillräckligt med tid att läsa eller interagera med sidan.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F41',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '2.2.2', [
            'type' => 'wai_failure',
            'code' => 'F16',
            'title_en' => 'Failure of Success Criterion 2.2.2 due to including scrolling content without a mechanism to pause and restart it',
            'title_sv' => 'Misslyckande på grund av rullande innehåll utan mekanism för att pausa och starta om',
            'description_en' => 'Scrolling, marquee-style content that starts automatically and cannot be paused prevents some users from reading it.',
            'description_sv' => 'Rullande innehåll som startar automatiskt och inte kan pausas gör att vissa användare inte hinner läsa det.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F16',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '2.5.1', [
            'type' => 'wai_failure',
            'code' => 'F105',
            'title_en' => 'Failure of Success Criterion 2.5.1 due to providing functionality via a path-based gesture without a simple pointer alternative',
            'title_sv' => 'Misslyckande på grund av att funktionalitet endast kan användas med en bangaserad gest',
            'description_en' => 'Requiring a swipe or drag gesture to perform an action, without a single-tap or click alternative, excludes users who cannot perform precise gestures.',
            'description_sv' => 'Att kräva en svep- eller dragrörelse för en åtgärd, utan alternativ med enkel tryckning eller klick, utestänger användare som inte kan utföra precisa gester.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F105',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '2.5.3', [
            'type' => 'wai_failure',
            'code' => 'F96',
            'title_en' => 'Failure due to the accessible name not containing the visible label text',
            'title_sv' => 'Misslyckande på grund av att det tillgängliga namnet inte innehåller den synliga etikettexten',
            'description_en' => 'When the accessible name differs from the visible label, speech-input users cannot reliably activate the control by speaking the label they see.',
            'description_sv' => 'När det tillgängliga namnet skiljer sig från den synliga etiketten kan röststyrningsanvändare inte tillförlitligt aktivera kontrollen genom att säga etiketten de ser.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F96',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '3.2.3', [
            'type' => 'wai_failure',
            'code' => 'F66',
            'title_en' => 'Failure of Success Criterion 3.2.3 due to presenting navigation links in a different relative order on different pages',
            'title_sv' => 'Misslyckande på grund av att navigeringslänkar visas i olika ordning på olika sidor',
            'description_en' => 'Repeated navigation mechanisms that change relative order between pages make it harder for users to predict where to find them.',
            'description_sv' => 'Upprepade navigeringsmekanismer som byter relativ ordning mellan sidor gör det svårare för användare att förutse var de finns.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F66',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '4.1.1', [
            'type' => 'wai_failure',
            'code' => 'F70',
            'title_en' => 'Failure of Success Criterion 4.1.1 due to incorrect use of start and end tags or attribute markup',
            'title_sv' => 'Misslyckande på grund av felaktig användning av start- och sluttaggar eller attributmarkering',
            'description_en' => 'Markup errors in element tags can cause assistive technologies to misinterpret or fail to expose content correctly.',
            'description_sv' => 'Felaktig markering i elementtaggar kan göra att hjälpmedelsteknik feltolkar eller inte korrekt exponerar innehållet.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F70',
            'order' => 1,
        ]);

        $addResource('WCAG 2.1', '4.1.1', [
            'type' => 'wai_failure',
            'code' => 'F77',
            'title_en' => 'Failure of Success Criterion 4.1.1 due to duplicate values of type ID',
            'title_sv' => 'Misslyckande på grund av dubblerade värden av typen ID',
            'description_en' => 'Duplicate id attributes are known to cause problems for assistive technologies interacting with the affected elements.',
            'description_sv' => 'Dubblerade id-attribut är kända för att orsaka problem för hjälpmedelsteknik som interagerar med de berörda elementen.',
            'url' => 'https://www.w3.org/WAI/WCAG21/Techniques/failures/F77',
            'order' => 2,
        ]);

        $diggSlugs = [
            '1.1.1' => 'beskriv-med-text-allt-innehall-som-inte-ar-text',
            '1.2.1' => 'erbjud-alternativ-till-inspelningar-som-bestar-av-enbart-ljud-eller-enbart-video',
            '1.2.2' => 'texta-ljudet-i-inspelade-filmer',
            '1.2.3' => 'syntolka-eller-erbjud-alternativ-till-videoinspelningar',
            '1.2.4' => 'teknik-for-hantering-av-undertexter',
            '1.2.5' => 'syntolka-videoinspelningar',
            '1.3.1' => 'formedla-information-struktur-och-relationer-i-koden',
            '1.3.2' => 'presentera-innehallet-i-en-meningsfull-ordning',
            '1.3.3' => 'gor-inte-instruktioner-beroende-av-sensoriska-kannetecken',
            '1.3.4' => 'se-till-att-innehallet-anpassas-efter-skarmens-riktning',
            '1.3.5' => 'ange-syftet-for-formularfalt-i-koden',
            '1.4.1' => 'anvand-inte-enbart-farg-for-att-formedla-information',
            '1.4.2' => 'gor-det-mojligt-att-pausa-stanga-av-eller-sanka-ljud',
            '1.4.3' => 'anvand-tillracklig-kontrast-mellan-text-och-bakgrund',
            '1.4.4' => 'se-till-att-text-gar-att-forstora',
            '1.4.5' => 'anvand-text-inte-bilder-av-text-for-att-visa-text',
            '1.4.10' => 'skapa-en-flexibel-layout-som-fungerar-vid-forstoring-eller-pa-liten-skarm',
            '1.4.11' => 'anvand-tillrackliga-kontraster-i-komponenter-och-grafik',
            '1.4.12' => 'se-till-att-det-gar-att-oka-avstand-mellan-tecken-rader-stycken-och-ord',
            '1.4.13' => 'innehall-som-visas-vid-hover-eller-fokus',
            '2.1.1' => 'all-funktionalitet-ska-kunna-anvandas-med-tangentbord',
            '2.1.2' => 'ingen-tangentbordsfalla',
            '2.1.4' => 'var-forsiktig-med-kortkommandon',
            '2.2.1' => 'gor-det-mojligt-att-justera-tidsbegransningar',
            '2.2.2' => 'gor-det-mojligt-att-pausa-eller-stanga-av-rorelser',
            '2.3.1' => 'orsaka-inte-epileptiska-anfall-genom-blinkande',
            '2.4.1' => 'gor-det-mojligt-att-hoppa-forbi-aterkommande-innehall',
            '2.4.2' => 'skriv-beskrivande-sidtitlar',
            '2.4.3' => 'ha-en-meningsfull-fokusordning',
            '2.4.4' => 'skriv-tydliga-lankar',
            '2.4.5' => 'erbjud-flera-satt-att-navigera',
            '2.4.6' => 'skriv-beskrivande-rubriker-och-ledtexter',
            '2.4.7' => 'gor-det-synligt-vad-som-ar-i-fokus',
            '2.5.1' => 'erbjud-alternativ-till-komplexa-finger--eller-pekarrorelser',
            '2.5.2' => 'gor-det-mojligt-att-angra-klick',
            '2.5.3' => 'se-till-att-text-pa-knappar-och-kontroller-overensstammer-med-maskinlasbara-namn',
            '2.5.4' => 'erbjud-alternativ-till-rorelsestyrning',
            '3.1.1' => 'ange-sidans-sprak-i-koden',
            '3.1.2' => 'ange-sprakforandringar-i-koden',
            '3.2.1' => 'ingen-kontextforandring-vid-fokus',
            '3.2.2' => 'utfor-inga-ovantade-forandringar-vid-inmatning',
            '3.2.3' => 'var-konsekvent-i-navigation',
            '3.2.4' => 'konsekvent-benamning',
            '3.3.1' => 'visa-var-ett-fel-uppstatt-och-beskriv-det-tydligt',
            '3.3.2' => 'skapa-tydliga-faltetiketter-ledtexter',
            '3.3.3' => 'ge-forslag-pa-hur-fel-kan-rattas-till',
            '3.3.4' => 'ge-mojlighet-att-angra-korrigera-eller-bekrafta-vid-viktiga-transaktioner',
            '4.1.1' => 'se-till-att-koden-validerar',
            '4.1.2' => 'se-till-att-komponenter-fungerar-i-hjalpmedel',
            '4.1.3' => 'se-till-att-hjalpmedel-kan-presentera-meddelanden-som-inte-ar-i-fokus',
        ];

        WcagSuccessCriterion::query()
            ->whereIn('standard', ['WCAG 2.1', 'WCAG 2.2'])
            ->orderBy('number')
            ->each(function (WcagSuccessCriterion $criterion) use ($addResource, $diggSlugs): void {
                $addResource($criterion->standard, $criterion->number, [
                    'type' => 'resource_link',
                    'title_en' => 'W3C: Understanding Success Criterion '.$criterion->number,
                    'title_sv' => 'W3C: Förståelse av framgångskriterium '.$criterion->number,
                    'description_en' => 'Official W3C Understanding guidance for this success criterion.',
                    'description_sv' => 'Officiell W3C-förståelse och vägledning för detta framgångskriterium.',
                    'url' => $criterion->url,
                    'order' => 10,
                ]);

                if (! isset($diggSlugs[$criterion->number])) {
                    return;
                }

                $diggUrl = 'https://www.digg.se/webbriktlinjer/alla-webbriktlinjer/'.$diggSlugs[$criterion->number];

                $addResource($criterion->standard, $criterion->number, [
                    'type' => 'resource_link',
                    'title_en' => 'DIGG: Web Guidelines - '.$criterion->number.' '.$criterion->name_en,
                    'title_sv' => 'DIGG: Webbriktlinjer - '.$criterion->number.' '.$criterion->name_sv,
                    'description_en' => 'Swedish public-sector guidance for digital accessibility.',
                    'description_sv' => 'Svensk vägledning för digital tillgänglighet i offentlig sektor.',
                    'url' => $diggUrl,
                    'order' => 11,
                ]);
            });

        WcagSuccessCriterion::query()
            ->where('standard', 'EN 301 549')
            ->orderBy('number')
            ->each(function (WcagSuccessCriterion $criterion) use ($addResource): void {
                $addResource('EN 301 549', $criterion->number, [
                    'type' => 'resource_link',
                    'title_en' => 'ETSI: EN 301 549 '.$criterion->number,
                    'title_sv' => 'ETSI: EN 301 549 '.$criterion->number,
                    'description_en' => 'Official ETSI source for this EN 301 549 requirement.',
                    'description_sv' => 'Officiell ETSI-källa för detta krav i EN 301 549.',
                    'url' => $criterion->url,
                    'order' => 10,
                ]);
            });
    }
}
