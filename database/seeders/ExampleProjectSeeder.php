<?php

namespace Database\Seeders;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\TestingMethodology;
use App\Models\User;
use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExampleProjectSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'test@example.com')
            ->firstOrFail();

        $project = AccessibilityProject::firstOrNew([
            'team_id' => $user->team_id,
            'name' => 'Nordiska E-tjänster',
        ]);
        $project->fill([
            'description' => 'Tillgänglighetsgranskning av en offentlig e-tjänst med publika sidor, inloggning och ett autentiserat betalflöde.',
            'target_wcag_level' => 'AA',
            'target_wcag_version' => '2.1',
            'audit_date' => now()->subDays(14),
            'status' => 'in_progress',
        ]);
        $this->saveWithUlid($project);

        $projectMember = ProjectMember::firstOrNew([
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
        $projectMember->fill(['role' => 'owner']);
        $this->saveWithUlid($projectMember);

        $pages = [];

        foreach ([
            [
                'key' => 'home',
                'name' => 'Startsida',
                'url' => 'https://portal.nordiska-etjanster.se/',
                'resource_type' => 'page',
                'access_context' => 'public',
                'description' => 'Publik startsida med huvudnavigation, kampanjkarusell och snabblänkar till vanliga tjänster.',
            ],
            [
                'key' => 'login',
                'name' => 'Logga in',
                'url' => 'https://portal.nordiska-etjanster.se/logga-in',
                'resource_type' => 'page',
                'access_context' => 'public',
                'description' => 'Inloggningssida för invånare som ska komma åt personliga ärenden.',
            ],
            [
                'key' => 'services',
                'name' => 'Mina tjänster',
                'url' => 'https://portal.nordiska-etjanster.se/mina-tjanster',
                'resource_type' => 'service',
                'access_context' => 'authenticated',
                'description' => 'Autentiserad översikt över pågående och avslutade tjänster.',
            ],
            [
                'key' => 'checkout',
                'name' => 'Ansökan och betalning',
                'url' => 'https://portal.nordiska-etjanster.se/ansokan/betalning',
                'resource_type' => 'service',
                'access_context' => 'authenticated',
                'description' => 'Flöde där användaren granskar uppgifter, korrigerar fel och betalar en avgift.',
            ],
            [
                'key' => 'help',
                'name' => 'Hjälp och kontakt',
                'url' => 'https://portal.nordiska-etjanster.se/hjalp',
                'resource_type' => 'page',
                'access_context' => 'public',
                'description' => 'Hjälpsida med vanliga frågor, kontaktvägar och länkar till guider.',
            ],
        ] as $pageData) {
            $page = AccessibilityPage::firstOrNew([
                'project_id' => $project->id,
                'url' => $pageData['url'],
            ]);
            $page->fill([
                'name' => $pageData['name'],
                'resource_type' => $pageData['resource_type'],
                'access_context' => $pageData['access_context'],
                'description' => $pageData['description'],
                'scope' => 'in_scope',
            ]);
            $this->saveWithUlid($page);
            $pages[$pageData['key']] = $page;
        }

        $issues = [
            [
                'page' => 'home',
                'title' => 'Huvudnavigationens menyknapp saknar tillgängligt namn',
                'description' => '<p>På mobil visas huvudnavigationen genom en ikonknapp utan tillgängligt namn. En skärmläsaranvändare får därför endast ”button” och kan inte avgöra om knappen öppnar eller stänger navigationen.</p><p>Problemet reproduceras i VoiceOver på iPhone och NVDA i Chrome.</p>',
                'severity' => 'critical',
                'difficulty' => 'easy',
                'component_area' => 'Huvudnavigation',
                'sample_scope' => 'all',
                'status' => 'open',
                'resolution_status' => 'open',
                'solution_suggestions' => '<p>Lägg till ett stabilt tillgängligt namn, exempelvis <code>aria-label="Öppna meny"</code>, och uppdatera namnet när menyn öppnas. Säkerställ även att knappens tillstånd exponeras med <code>aria-expanded</code>.</p>',
                'criteria' => [
                    ['number' => '4.1.2', 'failure_type' => 'manual', 'comment' => 'Interaktiv komponent saknar namn och exponerar inte aktuellt tillstånd.'],
                ],
            ],
            [
                'page' => 'home',
                'title' => 'Sekundär text och länkar har för låg kontrast',
                'description' => '<p>Grå text i informationskort och länkar i sidfoten har kontrast mellan 2,8:1 och 3,6:1 mot vit bakgrund. Färgen används även för text som förmedlar viktig information.</p><p>Mätningen är gjord med Colour Contrast Analyser i standardtemat.</p>',
                'severity' => 'major',
                'difficulty' => 'medium',
                'component_area' => 'Informationskort och sidfot',
                'sample_scope' => 'some',
                'status' => 'open',
                'resolution_status' => 'open',
                'solution_suggestions' => '<p>Byt till färgkombinationer som uppnår minst 4,5:1 för vanlig text och verifiera både standard- och hovringstillstånd.</p>',
                'criteria' => [
                    ['number' => '1.4.3', 'failure_type' => 'manual', 'comment' => 'Kontrastvärden understiger kravet för vanlig text.'],
                ],
            ],
            [
                'page' => 'home',
                'title' => 'Kampanjkarusellen kan inte pausas',
                'description' => '<p>En kampanjkarusell byter bild och fokusinnehåll automatiskt var femte sekund. Det finns ingen pausknapp och förändringen fortsätter medan användaren läser eller navigerar med tangentbord.</p>',
                'severity' => 'major',
                'difficulty' => 'medium',
                'component_area' => 'Kampanjkarusell',
                'sample_scope' => 'all',
                'status' => 'open',
                'resolution_status' => 'open',
                'solution_suggestions' => '<p>Erbjud paus, stopp eller dölj-funktion. Pausa också automatiskt när karusellen får fokus eller när användaren hovrar över den.</p>',
                'criteria' => [
                    ['number' => '2.2.2', 'failure_type' => 'F16', 'comment' => 'Rörligt innehåll startar automatiskt och saknar kontroll för paus eller stopp.'],
                ],
            ],
            [
                'page' => 'login',
                'title' => 'Inloggningsfält saknar syfte för automatisk komplettering',
                'description' => '<p>E-postfältet och lösenordsfältet använder generiska namn i HTML och saknar korrekt <code>autocomplete</code>-värde. Lösenordshanterare och hjälpmedel får därför begränsad information om fältens syfte.</p>',
                'severity' => 'moderate',
                'difficulty' => 'easy',
                'component_area' => 'Inloggningsformulär',
                'sample_scope' => 'all',
                'status' => 'open',
                'resolution_status' => 'open',
                'solution_suggestions' => '<p>Använd <code>autocomplete="username"</code> på användarnamnsfältet och <code>autocomplete="current-password"</code> på lösenordsfältet.</p>',
                'criteria' => [
                    ['number' => '1.3.5', 'failure_type' => 'manual', 'comment' => 'Formulärfält för personuppgifter saknar identifierbart programvarusyfte.'],
                ],
            ],
            [
                'page' => 'checkout',
                'title' => 'Valideringsfel är inte kopplade till formulärfälten',
                'description' => '<p>När ett obligatoriskt fält lämnas tomt visas ett rött meddelande ovanför formuläret. Meddelandet är inte kopplat med <code>aria-describedby</code> och fältet får inte <code>aria-invalid</code>.</p><p>En användare som navigerar med skärmläsare kan därför missa vilket fält som behöver korrigeras.</p>',
                'severity' => 'major',
                'difficulty' => 'medium',
                'component_area' => 'Betalningsformulär',
                'sample_scope' => 'some',
                'status' => 'open',
                'resolution_status' => 'open',
                'solution_suggestions' => '<p>Koppla feltexten till aktuellt fält med <code>aria-describedby</code>, markera fältet med <code>aria-invalid="true"</code> och flytta fokus till första felet när formuläret skickas.</p>',
                'criteria' => [
                    ['number' => '3.3.1', 'failure_type' => 'manual', 'comment' => 'Fel upptäcks visuellt men kommuniceras inte programmässigt till rätt fält.'],
                    ['number' => '3.3.3', 'failure_type' => 'manual', 'comment' => 'Felförslag saknas nära de fält som behöver korrigeras.'],
                ],
            ],
            [
                'page' => 'checkout',
                'title' => 'Fokus flyttas inte in i bekräftelsedialogen',
                'description' => '<p>Efter klick på ”Avsluta ansökan” visas en bekräftelsedialog. Fokus ligger kvar på knappen bakom dialogen och tangentbordsanvändaren kan tabba vidare till innehåll som visuellt är dolt.</p>',
                'severity' => 'major',
                'difficulty' => 'hard',
                'component_area' => 'Bekräftelsedialog',
                'sample_scope' => 'all',
                'status' => 'open',
                'resolution_status' => 'open',
                'solution_suggestions' => '<p>Flytta fokus till dialogens rubrik eller första kontroll, begränsa fokus till dialogen medan den är öppen och återställ fokus till utlösande knapp när dialogen stängs.</p>',
                'criteria' => [
                    ['number' => '2.4.3', 'failure_type' => 'manual', 'comment' => 'Tangentbordsfokus följer inte den visuella och funktionella ordningen.'],
                ],
            ],
            [
                'page' => 'services',
                'title' => 'Kortlänkarna använder samma generiska länktext',
                'description' => '<p>Flera kort på sidan ”Mina tjänster” avslutas med länktexten ”Läs mer”. När länkarna listas separat med skärmläsare går det inte att avgöra vilket ärende varje länk leder till.</p>',
                'severity' => 'minor',
                'difficulty' => 'easy',
                'component_area' => 'Tjänstekort',
                'sample_scope' => 'some',
                'status' => 'wont_fix',
                'resolution_status' => 'wontfix',
                'resolved_at' => now()->subDays(3),
                'resolution_notes' => 'Komponenten ersätts i nästa större designsystemsversion. Tillfällig ändring bedömdes ge inkonsekvent layout.',
                'solution_suggestions' => '<p>Använd beskrivande länktext, exempelvis ”Öppna bygglovsansökan”, eller komplettera länkens tillgängliga namn med ärendets rubrik.</p>',
                'criteria' => [
                    ['number' => '2.4.4', 'failure_type' => 'manual', 'comment' => 'Länkens syfte kan inte avgöras från länktexten i sammanhanget.'],
                ],
            ],
            [
                'page' => 'help',
                'title' => 'Rubriknivå hoppar från nivå två till nivå fyra',
                'description' => '<p>Hjälpsidans FAQ använder <code>h4</code> direkt under en <code>h2</code>. Den visuella storleken ser korrekt ut, men dokumentets rubrikhierarki blir svår att navigera med skärmläsare.</p>',
                'severity' => 'minor',
                'difficulty' => 'easy',
                'component_area' => 'FAQ',
                'sample_scope' => 'all',
                'status' => 'resolved',
                'resolution_status' => 'fixed',
                'resolved_at' => now()->subDays(5),
                'resolution_notes' => 'Rubriknivåerna justerades i komponentbiblioteket och verifierades med VoiceOver och NVDA.',
                'solution_suggestions' => '<p>Använd rubriknivåer i logisk ordning och låt visuell storlek styras av CSS i stället för felaktig semantisk nivå.</p>',
                'criteria' => [
                    ['number' => '1.3.1', 'failure_type' => 'manual', 'comment' => 'Rubrikstruktur uttrycker inte korrekt innehållshierarki.'],
                ],
            ],
        ];

        foreach ($issues as $issueData) {
            $issue = AccessibilityIssue::firstOrNew([
                'project_id' => $project->id,
                'title' => $issueData['title'],
            ]);
            $issue->fill([
                'page_id' => $pages[$issueData['page']]->id,
                'description' => $issueData['description'],
                'severity' => $issueData['severity'],
                'difficulty' => $issueData['difficulty'],
                'component_area' => $issueData['component_area'],
                'sample_scope' => $issueData['sample_scope'],
                'status' => $issueData['status'],
                'resolution_status' => $issueData['resolution_status'],
                'resolved_at' => $issueData['resolved_at'] ?? null,
                'resolution_notes' => $issueData['resolution_notes'] ?? null,
                'solution_suggestions' => $issueData['solution_suggestions'],
                'assigned_to' => $user->id,
                'assigned_at' => now()->subDays(12),
            ]);
            $this->saveWithUlid($issue);

            $criterionData = [];

            foreach ($issueData['criteria'] as $criterion) {
                $criterionModel = WcagSuccessCriterion::query()
                    ->where('standard', 'WCAG 2.1')
                    ->where('number', $criterion['number'])
                    ->firstOrFail();

                $criterionData[$criterionModel->id] = [
                    'failure_type' => $criterion['failure_type'],
                    'comment' => $criterion['comment'],
                ];
            }

            $issue->wcagCriteria()->sync($criterionData);
        }

        $methodologyNotes = [
            'NVDA' => 'NVDA 2025.1 med Chrome på Windows 11.',
            'Google Chrome' => 'Chrome 140 på Windows 11.',
            'VoiceOver (iOS)' => 'iPhone 15 med iOS 18.6.',
            'axe DevTools' => 'Automatiserad kontroll körd på samtliga sidor i scope.',
            'Keyboard-only navigation' => 'Manuell genomgång med Tab, Shift+Tab, Enter, Escape och piltangenter.',
            'Zoom (400%)' => 'Chrome zoom 400 % i 1280 px viewport.',
        ];
        $methodologies = [];

        foreach ($methodologyNotes as $name => $notes) {
            $methodology = TestingMethodology::query()
                ->where('name', $name)
                ->firstOrFail();

            $methodologies[$methodology->id] = [
                'id' => Str::ulid(),
                'notes' => $notes,
            ];
        }

        $project->methodologies()->sync($methodologies);
    }

    private function saveWithUlid(AccessibilityProject|AccessibilityPage|AccessibilityIssue|ProjectMember $model): void
    {
        if (! $model->exists) {
            $model->id = Str::ulid();
        }

        $model->save();
    }
}
