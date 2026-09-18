<?php

namespace Database\Seeders;

use App\Models\WcagSuccessCriterion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WcagSuccessCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criteria = [
            ['number' => '1.1.1', 'level' => 'A', 'name_en' => 'Non-text Content', 'name_sv' => 'Icke-textinnehål', 'description_en' => 'All non-text content that is presented to the user has a text alternative that serves the equivalent purpose.', 'description_sv' => 'Allt icke-textinnehål som presenteras för användaren har en textalternativ som uppfyller motsvarande syfte.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.1.1.html'],
            ['number' => '1.2.1', 'level' => 'A', 'name_en' => 'Audio-only and Video-only (Prerecorded)', 'name_sv' => 'Endast ljud och endast video (inspelad)', 'description_en' => 'For prerecorded audio-only and prerecorded video-only media, an alternative or an audio track is provided.', 'description_sv' => 'För inspelad endast-ljud och inspelad endast-video-media tillhandahålls ett alternativ eller ett ljudspår.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.2.1.html'],
            ['number' => '1.2.2', 'level' => 'A', 'name_en' => 'Captions (Prerecorded)', 'name_sv' => 'Textning (inspelad)', 'description_en' => 'Captions are provided for all prerecorded audio content in synchronized media.', 'description_sv' => 'Textning tillhandahålls för allt inspelat audioinnehål i synkroniserad media.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.2.2.html'],
            ['number' => '1.2.3', 'level' => 'A', 'name_en' => 'Audio Description or Media Alternative (Prerecorded)', 'name_sv' => 'Audiobeskrivning eller mediealternativ (inspelad)', 'description_en' => 'An alternative or audio description is provided for all prerecorded video content in synchronized media.', 'description_sv' => 'Ett alternativ eller audiobeskrivning tillhandahålls för allt inspelat videoinnehål i synkroniserad media.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.2.3.html'],
            ['number' => '1.2.4', 'level' => 'AA', 'name_en' => 'Captions (Live)', 'name_sv' => 'Textning (direkt)', 'description_en' => 'Captions are provided for all live audio content in synchronized media.', 'description_sv' => 'Textning tillhandahålls för allt direktljudinnehål i synkroniserad media.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.2.4.html'],
            ['number' => '1.2.5', 'level' => 'AA', 'name_en' => 'Audio Description (Prerecorded)', 'name_sv' => 'Audiobeskrivning (inspelad)', 'description_en' => 'Audio description is provided for all prerecorded video content in synchronized media.', 'description_sv' => 'Audiobeskrivning tillhandahålls för allt inspelat videoinnehål i synkroniserad media.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.2.5.html'],
            ['number' => '1.3.1', 'level' => 'A', 'name_en' => 'Info and Relationships', 'name_sv' => 'Information och relationer', 'description_en' => 'Information, structure, and relationships conveyed through presentation can be programmatically determined or are available in text.', 'description_sv' => 'Information, struktur och relationer som förmedlas genom presentationen kan programmässigt bestämmas eller finns tillgänglig i text.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.3.1.html'],
            ['number' => '1.3.2', 'level' => 'A', 'name_en' => 'Meaningful Sequence', 'name_sv' => 'Meningsfull ordning', 'description_en' => 'When the sequence in which content is presented affects its meaning, a correct reading sequence can be programmatically determined.', 'description_sv' => 'När ordningen i vilken innehåll presenteras påverkar dess betydelse kan en korrekt läsordning programmässigt bestämmas.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.3.2.html'],
            ['number' => '1.3.3', 'level' => 'A', 'name_en' => 'Sensory Characteristics', 'name_sv' => 'Sensoriska egenskaper', 'description_en' => 'Instructions provided for understanding and operating content do not rely solely on the shape, size, or visual location of components.', 'description_sv' => 'Instruktioner för förståelse och användning av innehåll förlitar sig inte enbart på form, storlek eller visuell plats för komponenter.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.3.3.html'],
            ['number' => '1.3.4', 'level' => 'AA', 'name_en' => 'Orientation', 'name_sv' => 'Orientering', 'description_en' => 'Content does not restrict its view and operation to a single display orientation, such as portrait or landscape, unless a specific orientation is essential.', 'description_sv' => 'Innehåll begränsar inte visning och användning till en enda skärmorientering, till exempel stående eller liggande, om inte en specifik orientering är avgörande.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/orientation.html'],
            ['number' => '1.3.5', 'level' => 'AA', 'name_en' => 'Identify Input Purpose', 'name_sv' => 'Identifiera inmatningens syfte', 'description_en' => 'The purpose of each input field collecting information about the user can be programmatically determined when the technology supports it.', 'description_sv' => 'Syftet med varje inmatningsfält som samlar in information om användaren kan bestämmas programmässigt när tekniken stödjer det.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/identify-input-purpose.html'],
            ['number' => '1.4.1', 'level' => 'A', 'name_en' => 'Use of Color', 'name_sv' => 'Färganvändning', 'description_en' => 'Color is not used as the only visual means of conveying information, indicating an action, prompting a response, or distinguishing a visual element.', 'description_sv' => 'Färg används inte som det enda visuella sättet att förmedla information, indikera en åtgärd, fråga efter ett svar eller skilja ett visuellt element.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.4.1.html'],
            ['number' => '1.4.2', 'level' => 'A', 'name_en' => 'Audio Control', 'name_sv' => 'Audiokontroll', 'description_en' => 'If any audio on a Web page plays automatically for more than 3 seconds, either a mechanism is available to pause or stop the audio, or a mechanism is available to control audio volume.', 'description_sv' => 'Om något ljud på en webbsida spelas automatiskt i mer än 3 sekunder finns det antingen en mekanism för att pausa eller stoppa ljudet, eller en mekanism för att kontrollera ljudvolymen.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.4.2.html'],
            ['number' => '1.4.3', 'level' => 'AA', 'name_en' => 'Contrast (Minimum)', 'name_sv' => 'Kontrast (minimum)', 'description_en' => 'The visual presentation of text and images of text has a contrast ratio of at least 4.5:1.', 'description_sv' => 'Den visuella presentationen av text och textbilder har ett kontrastförhållande på minst 4,5:1.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.4.3.html'],
            ['number' => '1.4.4', 'level' => 'AA', 'name_en' => 'Resize text', 'name_sv' => 'Ändra storlek på text', 'description_en' => 'Except for captions and images of text, text can be resized without loss of content or functionality up to 200 percent.', 'description_sv' => 'Förutom bildtexter och textbilder kan text ändras upp till 200 procent utan förlust av innehål eller funktionalitet.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/1.4.4.html'],
            ['number' => '1.4.5', 'level' => 'AA', 'name_en' => 'Images of Text', 'name_sv' => 'Textbilder', 'description_en' => 'If the technologies being used can achieve the visual presentation, text is used to convey information rather than images of text, except where a particular presentation is essential or customizable.', 'description_sv' => 'Om tekniken som används kan uppnå den visuella presentationen används text för att förmedla information istället för textbilder, förutom när en viss presentation är avgörande eller anpassningsbar.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/images-of-text.html'],
            ['number' => '1.4.10', 'level' => 'AA', 'name_en' => 'Reflow', 'name_sv' => 'Radbrytning', 'description_en' => 'Content can be presented without loss of information or functionality and without requiring scrolling in two dimensions, except for content that requires two-dimensional layout for usage or meaning.', 'description_sv' => 'Innehåll kan presenteras utan förlust av information eller funktionalitet och utan att kräva rullning i två dimensioner, förutom innehåll som kräver tvådimensionell layout för användning eller betydelse.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/reflow.html'],
            ['number' => '1.4.11', 'level' => 'AA', 'name_en' => 'Non-text Contrast', 'name_sv' => 'Kontrast för icke-textinnehåll', 'description_en' => 'The visual presentation of user interface components and graphical objects required to understand content has a contrast ratio of at least 3:1 against adjacent colors.', 'description_sv' => 'Den visuella presentationen av användarinterfacekomponenter och grafiska objekt som krävs för att förstå innehållet har ett kontrastförhållande på minst 3:1 mot intilliggande färger.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/non-text-contrast.html'],
            ['number' => '1.4.12', 'level' => 'AA', 'name_en' => 'Text Spacing', 'name_sv' => 'Textavstånd', 'description_en' => 'No loss of content or functionality occurs when users adjust line height, paragraph spacing, letter spacing, and word spacing to specified minimum values.', 'description_sv' => 'Ingen förlust av innehåll eller funktionalitet uppstår när användare justerar radavstånd, styckeavstånd, bokstavsavstånd och ordavstånd till angivna minimivärden.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/text-spacing.html'],
            ['number' => '1.4.13', 'level' => 'AA', 'name_en' => 'Content on Hover or Focus', 'name_sv' => 'Innehåll vid hovring eller fokus', 'description_en' => 'Additional content that appears on hover or keyboard focus can be dismissed without moving the pointer, remains visible while hovered or focused, and stays visible until dismissed or no longer relevant.', 'description_sv' => 'Ytterligare innehåll som visas vid hovring eller tangentbordsfokus kan stängas utan att flytta pekaren, förblir synligt vid hovring eller fokus, och är kvar synligt tills det stängs eller inte längre är relevant.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/content-on-hover-or-focus.html'],
            ['number' => '2.1.1', 'level' => 'A', 'name_en' => 'Keyboard', 'name_sv' => 'Tangentbord', 'description_en' => 'All functionality of the content is operable via a keyboard interface without requiring specific timings for individual keystrokes.', 'description_sv' => 'All funktionalitet för innehållet kan användas via ett tangentbordsgränssnitt utan att kräva specifik timing för individuella tangenttryckningar.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.1.1.html'],
            ['number' => '2.1.2', 'level' => 'A', 'name_en' => 'No Keyboard Trap', 'name_sv' => 'Ingen tangentbordsfälla', 'description_en' => 'If keyboard focus can be moved to a component of the page using a keyboard interface, then focus can be moved away from that component using only a keyboard interface.', 'description_sv' => 'Om tangentbordsfokus kan flyttas till en del av sidan med ett tangentbordsgränssnitt kan fokus flyttas bort från den komponenten med endast ett tangentbordsgränssnitt.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.1.2.html'],
            ['number' => '2.1.4', 'level' => 'A', 'name_en' => 'Character Key Shortcuts', 'name_sv' => 'Tangentbordsgenvägar med enskilda tecken', 'description_en' => 'If a keyboard shortcut uses only letter, punctuation, number, or symbol characters, users can turn it off, remap it, or it is only active when the relevant component has focus.', 'description_sv' => 'Om en tangentbordsgenväg endast använder bokstäver, skiljetecken, siffror eller symboler kan användare stänga av den, mappa om den, eller så är den endast aktiv när den berörda komponenten har fokus.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/character-key-shortcuts.html'],
            ['number' => '2.2.1', 'level' => 'A', 'name_en' => 'Timing Adjustable', 'name_sv' => 'Justerbar tidsgräns', 'description_en' => 'For each time limit set by the content, users can turn it off, adjust it, or extend it, unless it is a real-time event, essential, or longer than 20 hours.', 'description_sv' => 'För varje tidsgräns som ställs in av innehållet kan användare stänga av, justera eller förlänga den, om den inte är en direktsändning, avgörande, eller längre än 20 timmar.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/timing-adjustable.html'],
            ['number' => '2.2.2', 'level' => 'A', 'name_en' => 'Pause, Stop, Hide', 'name_sv' => 'Pausa, stoppa, dölj', 'description_en' => 'Users can pause, stop, or hide moving, blinking, scrolling, or auto-updating content that starts automatically, lasts more than five seconds, and is presented alongside other content.', 'description_sv' => 'Användare kan pausa, stoppa eller dölja rörligt, blinkande, rullande eller automatiskt uppdaterande innehåll som startar automatiskt, varar mer än fem sekunder och visas tillsammans med annat innehåll.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/pause-stop-hide.html'],
            ['number' => '2.3.1', 'level' => 'A', 'name_en' => 'Three Flashes or Below Threshold', 'name_sv' => 'Tre blinkningar eller under gränsvärdet', 'description_en' => 'Web pages do not contain anything that flashes more than three times in any one-second period, or the flash is below the general flash and red flash thresholds.', 'description_sv' => 'Webbsidor innehåller inget som blinkar mer än tre gånger under en sekund, eller så ligger blinkningen under gränsvärdena för allmän blinkning och röd blinkning.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/three-flashes-or-below-threshold.html'],
            ['number' => '2.4.1', 'level' => 'A', 'name_en' => 'Bypass Blocks', 'name_sv' => 'Kringgå block', 'description_en' => 'A mechanism is available to bypass blocks of content that are repeated on multiple Web pages.', 'description_sv' => 'En mekanism finns tillgänglig för att kringgå block av innehål som upprepas på flera webbsidor.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.4.1.html'],
            ['number' => '2.4.2', 'level' => 'A', 'name_en' => 'Page Titled', 'name_sv' => 'Sidan är rubricerad', 'description_en' => 'Web pages have titles that describe topic or purpose.', 'description_sv' => 'Webbsidor har titlar som beskriver ämne eller syfte.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.4.2.html'],
            ['number' => '2.4.3', 'level' => 'A', 'name_en' => 'Focus Order', 'name_sv' => 'Fokusordning', 'description_en' => 'If a Web page can be navigated sequentially and the navigation sequences affect meaning or operation, focusable components receive focus in an order that preserves meaning and operability.', 'description_sv' => 'Om en webbsida kan navigeras sekventiellt och navigationsekvenserna påverkar betydelse eller användning får fokuserbara komponenter fokus i en ordning som bevarar betydelse och användbarhet.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.4.3.html'],
            ['number' => '2.4.4', 'level' => 'A', 'name_en' => 'Link Purpose (In Context)', 'name_sv' => 'Länkens syfte (i sammanhang)', 'description_en' => 'The purpose of each link can be determined from the link text alone or from the link text together with its programmatically determined link context.', 'description_sv' => 'Syftet med varje länk kan bestämmas från länktexten ensam eller från länktexten tillsammans med sitt programmässigt bestämda länksammanhang.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.4.4.html'],
            ['number' => '2.4.5', 'level' => 'AA', 'name_en' => 'Multiple Ways', 'name_sv' => 'Flera sätt', 'description_en' => 'More than one way is available to locate a Web page within a set of Web pages, except where the page is the result of, or a step in, a process.', 'description_sv' => 'Fler än ett sätt finns tillgängligt för att hitta en webbsida inom en uppsättning webbsidor, förutom när sidan är resultatet av eller ett steg i en process.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/multiple-ways.html'],
            ['number' => '2.4.6', 'level' => 'AA', 'name_en' => 'Headings and Labels', 'name_sv' => 'Rubriker och etiketter', 'description_en' => 'Headings and labels describe topic or purpose.', 'description_sv' => 'Rubriker och etiketter beskriver ämne eller syfte.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/headings-and-labels.html'],
            ['number' => '2.4.7', 'level' => 'AA', 'name_en' => 'Focus Visible', 'name_sv' => 'Fokus synligt', 'description_en' => 'Any keyboard operable user interface has a mode of operation where the keyboard focus indicator is visible.', 'description_sv' => 'Alla användarinterfaces som kan användas med tangentbord har ett driftläge där tangentbordsfokusindikatorn är synlig.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/2.4.7.html'],
            ['number' => '2.5.1', 'level' => 'A', 'name_en' => 'Pointer Gestures', 'name_sv' => 'Pekargester', 'description_en' => 'All functionality that uses multipoint or path-based gestures for operation can be operated with a single pointer without a path-based gesture, unless essential.', 'description_sv' => 'All funktionalitet som använder flerpunkts- eller bangaserade gester kan användas med en enda pekare utan en bangaserad gest, om det inte är avgörande.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/pointer-gestures.html'],
            ['number' => '2.5.2', 'level' => 'A', 'name_en' => 'Pointer Cancellation', 'name_sv' => 'Avbryta pekarhändelse', 'description_en' => 'For functionality operated using a single pointer, activation does not occur on the down-event, or can be aborted or undone, or the up-event reverses any outcome, unless completing the function on the down-event is essential.', 'description_sv' => 'För funktionalitet som styrs med en enda pekare sker aktivering inte vid nedtryckningen, eller kan avbrytas eller ångras, eller så återställer uppsläppet resultatet, om det inte är avgörande att slutföra funktionen vid nedtryckningen.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/pointer-cancellation.html'],
            ['number' => '2.5.3', 'level' => 'A', 'name_en' => 'Label in Name', 'name_sv' => 'Etikett i namn', 'description_en' => 'For user interface components with labels that include text or images of text, the accessible name contains the visible label text.', 'description_sv' => 'För användarinterfacekomponenter med etiketter som innehåller text eller textbilder innehåller det tillgängliga namnet den synliga etikettexten.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/label-in-name.html'],
            ['number' => '2.5.4', 'level' => 'A', 'name_en' => 'Motion Actuation', 'name_sv' => 'Rörelseaktivering', 'description_en' => 'Functionality that can be operated by device motion or user motion can also be operated by user interface components and can be disabled, except where the motion is essential or used through an accessibility-supported interface.', 'description_sv' => 'Funktionalitet som kan styras med enhetsrörelse eller användarrörelse kan också styras med användarinterfacekomponenter och kan inaktiveras, förutom när rörelsen är avgörande eller används genom ett tillgänglighetsstött gränssnitt.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/motion-actuation.html'],
            ['number' => '3.1.1', 'level' => 'A', 'name_en' => 'Language of Page', 'name_sv' => 'Språk för sida', 'description_en' => 'The default human language of each Web page can be programmatically determined.', 'description_sv' => 'Det förvalda människokspråket för varje webbsida kan programmässigt bestämmas.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/3.1.1.html'],
            ['number' => '3.1.2', 'level' => 'AA', 'name_en' => 'Language of Parts', 'name_sv' => 'Språk för delar', 'description_en' => 'The human language of each passage or phrase in the content can be programmatically determined, except for proper names, technical terms, and words of indeterminate language.', 'description_sv' => 'Människospråket för varje avsnitt eller fras i innehållet kan bestämmas programmässigt, förutom för egennamn, tekniska termer och ord med obestämt språk.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/language-of-parts.html'],
            ['number' => '3.2.1', 'level' => 'A', 'name_en' => 'On Focus', 'name_sv' => 'Vid fokus', 'description_en' => 'When any component receives focus, it does not initiate a change of context.', 'description_sv' => 'När någon komponent får fokus initierar den inte en kontextförändring.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/3.2.1.html'],
            ['number' => '3.2.2', 'level' => 'A', 'name_en' => 'On Input', 'name_sv' => 'Vid inmatning', 'description_en' => 'Changing the setting of any user interface component does not automatically cause a change of context unless the user has been advised of the behavior.', 'description_sv' => 'Ändring av inställningen för någon användarinterfacekomponent orsakar inte automatiskt en kontextförändring om inte användaren har underrättats om beteendet.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/3.2.2.html'],
            ['number' => '3.2.3', 'level' => 'AA', 'name_en' => 'Consistent Navigation', 'name_sv' => 'Konsekvent navigering', 'description_en' => 'Navigational mechanisms repeated on multiple Web pages within a set occur in the same relative order each time, unless a change is initiated by the user.', 'description_sv' => 'Navigeringsmekanismer som upprepas på flera webbsidor inom en uppsättning förekommer i samma relativa ordning varje gång, om inte ändringen initieras av användaren.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/consistent-navigation.html'],
            ['number' => '3.2.4', 'level' => 'AA', 'name_en' => 'Consistent Identification', 'name_sv' => 'Konsekvent identifiering', 'description_en' => 'Components that have the same functionality within a set of Web pages are identified consistently.', 'description_sv' => 'Komponenter som har samma funktionalitet inom en uppsättning webbsidor identifieras konsekvent.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/consistent-identification.html'],
            ['number' => '3.3.1', 'level' => 'A', 'name_en' => 'Error Identification', 'name_sv' => 'Felidentifiering', 'description_en' => 'If an input error is automatically detected, the item that is in error is identified and the error is described to the user in text.', 'description_sv' => 'Om ett inmatningsfel automatiskt upptäcks identifieras objektet som är felaktigt och felet beskrivs för användaren i text.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/3.3.1.html'],
            ['number' => '3.3.2', 'level' => 'A', 'name_en' => 'Labels or Instructions', 'name_sv' => 'Etiketter eller instruktioner', 'description_en' => 'Labels or instructions are provided when content requires user input.', 'description_sv' => 'Etiketter eller instruktioner tillhandahålls när innehål kräver användarinmatning.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/3.3.2.html'],
            ['number' => '3.3.3', 'level' => 'AA', 'name_en' => 'Error Suggestion', 'name_sv' => 'Felförslag', 'description_en' => 'If an input error is automatically detected and suggestions for correction are known, the suggestions are provided to the user, unless it would jeopardize the security or purpose of the content.', 'description_sv' => 'Om ett inmatningsfel automatiskt upptäcks och förslag på korrigering är kända ges förslagen till användaren, om det inte skulle äventyra säkerheten eller syftet med innehållet.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/error-suggestion.html'],
            ['number' => '3.3.4', 'level' => 'AA', 'name_en' => 'Error Prevention (Legal, Financial, Data)', 'name_sv' => 'Felförebyggande (juridiskt, ekonomiskt, data)', 'description_en' => 'For pages that cause legal commitments, financial transactions, or modify or delete user-controllable data, submissions are reversible, checked with an opportunity to correct errors, or confirmed before finalizing.', 'description_sv' => 'För sidor som medför juridiska åtaganden, ekonomiska transaktioner eller ändrar eller raderar användarstyrd data är inlämningar återkalleliga, kontrolleras med möjlighet att rätta fel, eller bekräftas innan de slutförs.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/error-prevention-legal-financial-data.html'],
            ['number' => '4.1.1', 'level' => 'A', 'name_en' => 'Parsing', 'name_sv' => 'Tolkning', 'description_en' => 'In content implemented using markup languages, elements have complete start and end tags, are nested according to specification, do not contain duplicate attributes, and any IDs are unique.', 'description_sv' => 'I innehåll som implementeras med markeringsspråk har element kompletta start- och sluttaggar, är korrekt nästlade enligt specifikationen, innehåller inga dubblerade attribut, och alla ID är unika.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/parsing.html'],
            ['number' => '4.1.2', 'level' => 'A', 'name_en' => 'Name, Role, Value', 'name_sv' => 'Namn, roll, värde', 'description_en' => 'For all user interface components, the name and role can be programmatically determined; states, properties, and values that can be set by the user can be programmatically set; and notification of changes to these items is available to user agents, including assistive technologies.', 'description_sv' => 'För alla användarinterfacekomponenter kan namn och roll programmässigt bestämmas; tillstånd, egenskaper och värden som kan ställas av användaren kan programmässigt ställas in; och meddelande om ändringar av dessa objekt är tillgängligt för användaragenter, inklusive hjälpmedelsteknologi.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/4.1.2.html'],
            ['number' => '4.1.3', 'level' => 'AA', 'name_en' => 'Status Messages', 'name_sv' => 'Statusmeddelanden', 'description_en' => 'In content implemented using markup languages, status messages can be programmatically determined through role or properties such that they can be presented to the user by assistive technologies without receiving focus.', 'description_sv' => 'I innehål implementerat med markeringsspråk kan statusmeddelanden programmässigt bestämmas genom roll eller egenskaper så att de kan presenteras för användaren av hjälpmedelsteknologi utan att få fokus.', 'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/4.1.3.html'],
        ];

        $w3cSlugs = [
            '1.1.1' => 'non-text-content',
            '1.2.1' => 'audio-only-and-video-only-prerecorded',
            '1.2.2' => 'captions-prerecorded',
            '1.2.3' => 'audio-description-or-media-alternative-prerecorded',
            '1.2.4' => 'captions-live',
            '1.2.5' => 'audio-description-prerecorded',
            '1.3.1' => 'info-and-relationships',
            '1.3.2' => 'meaningful-sequence',
            '1.3.3' => 'sensory-characteristics',
            '1.3.4' => 'orientation',
            '1.3.5' => 'identify-input-purpose',
            '1.4.1' => 'use-of-color',
            '1.4.2' => 'audio-control',
            '1.4.3' => 'contrast-minimum',
            '1.4.4' => 'resize-text',
            '1.4.5' => 'images-of-text',
            '1.4.10' => 'reflow',
            '1.4.11' => 'non-text-contrast',
            '1.4.12' => 'text-spacing',
            '1.4.13' => 'content-on-hover-or-focus',
            '2.1.1' => 'keyboard',
            '2.1.2' => 'no-keyboard-trap',
            '2.1.4' => 'character-key-shortcuts',
            '2.2.1' => 'timing-adjustable',
            '2.2.2' => 'pause-stop-hide',
            '2.3.1' => 'three-flashes-or-below-threshold',
            '2.4.1' => 'bypass-blocks',
            '2.4.2' => 'page-titled',
            '2.4.3' => 'focus-order',
            '2.4.4' => 'link-purpose-in-context',
            '2.4.5' => 'multiple-ways',
            '2.4.6' => 'headings-and-labels',
            '2.4.7' => 'focus-visible',
            '2.5.1' => 'pointer-gestures',
            '2.5.2' => 'pointer-cancellation',
            '2.5.3' => 'label-in-name',
            '2.5.4' => 'motion-actuation',
            '3.1.1' => 'language-of-page',
            '3.1.2' => 'language-of-parts',
            '3.2.1' => 'on-focus',
            '3.2.2' => 'on-input',
            '3.2.3' => 'consistent-navigation',
            '3.2.4' => 'consistent-identification',
            '3.3.1' => 'error-identification',
            '3.3.2' => 'labels-or-instructions',
            '3.3.3' => 'error-suggestion',
            '3.3.4' => 'error-prevention-legal-financial-data',
            '4.1.1' => 'parsing',
            '4.1.2' => 'name-role-value',
            '4.1.3' => 'status-messages',
        ];

        $wcag21Criteria = array_map(function (array $criterion) use ($w3cSlugs): array {
            return [
                ...$criterion,
                'standard' => 'WCAG 2.1',
                'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/'.$w3cSlugs[$criterion['number']],
            ];
        }, $criteria);

        $wcag22Criteria = array_values(array_filter(
            $wcag21Criteria,
            fn (array $criterion): bool => $criterion['number'] !== '4.1.1',
        ));

        foreach ($wcag22Criteria as &$criterion) {
            $criterion['standard'] = 'WCAG 2.2';
            $criterion['url'] = str_replace('WCAG21', 'WCAG22', $criterion['url']);
        }
        unset($criterion);

        $wcag22Criteria = [
            ...$wcag22Criteria,
            [
                'standard' => 'WCAG 2.2',
                'number' => '2.4.11',
                'level' => 'AA',
                'name_en' => 'Focus Not Obscured (Minimum)',
                'name_sv' => 'Fokus inte dolt (minimum)',
                'description_en' => 'When a user interface component receives keyboard focus, it is not entirely hidden due to author-created content.',
                'description_sv' => 'När en användargränssnittskomponent får tangentbordsfokus är den inte helt dold av innehåll som skapats av författaren.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-minimum',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '2.4.12',
                'level' => 'AAA',
                'name_en' => 'Focus Not Obscured (Enhanced)',
                'name_sv' => 'Fokus inte dolt (förstärkt)',
                'description_en' => 'When a user interface component receives keyboard focus, no part of the component is hidden by author-created content.',
                'description_sv' => 'När en användargränssnittskomponent får tangentbordsfokus är ingen del av komponenten dold av innehåll som skapats av författaren.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-enhanced',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '2.4.13',
                'level' => 'AAA',
                'name_en' => 'Focus Appearance',
                'name_sv' => 'Fokusutseende',
                'description_en' => 'Visible keyboard focus indicators meet minimum size and contrast requirements.',
                'description_sv' => 'Synliga tangentbordsfokusindikatorer uppfyller minimikraven för storlek och kontrast.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/focus-appearance',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '2.5.7',
                'level' => 'AA',
                'name_en' => 'Dragging Movements',
                'name_sv' => 'Dragrörelser',
                'description_en' => 'Functionality that uses dragging can also be operated with a single pointer without dragging, unless dragging is essential.',
                'description_sv' => 'Funktionalitet som använder dragning kan också användas med en enda pekare utan dragning, om dragning inte är avgörande.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/dragging-movements',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '2.5.8',
                'level' => 'AA',
                'name_en' => 'Target Size (Minimum)',
                'name_sv' => 'Målstorlek (minimum)',
                'description_en' => 'Pointer input targets are at least 24 by 24 CSS pixels, subject to defined exceptions.',
                'description_sv' => 'Mål för pekarinmatning är minst 24 gånger 24 CSS-pixlar, med angivna undantag.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '3.2.6',
                'level' => 'A',
                'name_en' => 'Consistent Help',
                'name_sv' => 'Konsekvent hjälp',
                'description_en' => 'Repeated help mechanisms occur in the same relative order across pages in a set of web pages.',
                'description_sv' => 'Upprepade hjälpfunktioner förekommer i samma relativa ordning på sidor inom en uppsättning webbsidor.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/consistent-help',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '3.3.7',
                'level' => 'A',
                'name_en' => 'Redundant Entry',
                'name_sv' => 'Upprepad inmatning',
                'description_en' => 'Information previously entered in the same process is auto-populated or available for selection, unless an exception applies.',
                'description_sv' => 'Information som tidigare angetts i samma process fylls i automatiskt eller finns tillgänglig för val, om inget undantag gäller.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/redundant-entry',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '3.3.8',
                'level' => 'AA',
                'name_en' => 'Accessible Authentication (Minimum)',
                'name_sv' => 'Tillgänglig autentisering (minimum)',
                'description_en' => 'An authentication step does not require a cognitive function test unless an accessible alternative or exception applies.',
                'description_sv' => 'Ett autentiseringssteg kräver inte ett kognitivt funktionstest om inte ett tillgängligt alternativ eller undantag gäller.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/accessible-authentication-minimum',
            ],
            [
                'standard' => 'WCAG 2.2',
                'number' => '3.3.9',
                'level' => 'AAA',
                'name_en' => 'Accessible Authentication (Enhanced)',
                'name_sv' => 'Tillgänglig autentisering (förstärkt)',
                'description_en' => 'No cognitive function test is required for authentication unless an exception applies.',
                'description_sv' => 'Inget kognitivt funktionstest krävs för autentisering om inte ett undantag gäller.',
                'url' => 'https://www.w3.org/WAI/WCAG22/Understanding/accessible-authentication-enhanced',
            ],
        ];

        $en301549Criteria = [
            ['number' => '5.1', 'name_en' => 'Functional performance', 'name_sv' => 'Funktionell prestanda', 'description_en' => 'ICT provides accessible operation for people with a range of disabilities, including where personal assistive technology cannot be attached.', 'description_sv' => 'IKT erbjuder tillgänglig användning för personer med olika funktionsnedsättningar, även när personliga hjälpmedel inte kan anslutas.'],
            ['number' => '5.2', 'name_en' => 'Activation of accessibility features', 'name_sv' => 'Aktivering av tillgänglighetsfunktioner', 'description_en' => 'Documented accessibility features can be activated without relying on a method that does not support the relevant need.', 'description_sv' => 'Dokumenterade tillgänglighetsfunktioner kan aktiveras utan att förlita sig på en metod som inte stödjer det relevanta behovet.'],
            ['number' => '5.3', 'name_en' => 'Biometrics', 'name_sv' => 'Biometri', 'description_en' => 'ICT does not rely on a biological characteristic as the only means of user identification or control.', 'description_sv' => 'IKT förlitar sig inte på en biologisk egenskap som enda sätt att identifiera användaren eller styra IKT.'],
            ['number' => '5.4', 'name_en' => 'Preservation of accessibility information', 'name_sv' => 'Bevarande av tillgänglighetsinformation', 'description_en' => 'Accessibility information is preserved when ICT converts information between formats or media.', 'description_sv' => 'Tillgänglighetsinformation bevaras när IKT konverterar information mellan format eller medier.'],
            ['number' => '5.6', 'name_en' => 'Locking or toggle controls', 'name_sv' => 'Lås- och växlingskontroller', 'description_en' => 'The state of locking and toggle controls is discernible without relying only on vision.', 'description_sv' => 'Tillståndet för lås- och växlingskontroller kan uppfattas utan att enbart förlita sig på synen.'],
            ['number' => '5.7', 'name_en' => 'Key repeat', 'name_sv' => 'Tangentupprepning', 'description_en' => 'Key repeat behavior can be controlled or does not prevent accessible operation.', 'description_sv' => 'Tangentupprepning kan kontrolleras eller hindrar inte tillgänglig användning.'],
            ['number' => '5.8', 'name_en' => 'Double-strike key acceptance', 'name_sv' => 'Godkännande av dubbla tangenttryckningar', 'description_en' => 'Accidental repeated key presses do not prevent users from completing the intended operation.', 'description_sv' => 'Oavsiktliga upprepade tangenttryckningar hindrar inte användare från att slutföra avsedd användning.'],
            ['number' => '5.9', 'name_en' => 'Simultaneous user actions', 'name_sv' => 'Samtidiga användaråtgärder', 'description_en' => 'ICT supports applicable simultaneous or sequential user actions needed for accessible operation.', 'description_sv' => 'IKT stödjer tillämpliga samtidiga eller sekventiella användaråtgärder som krävs för tillgänglig användning.'],
            ['number' => '5.10', 'name_en' => 'Privacy', 'name_sv' => 'Integritet', 'description_en' => 'Accessibility features do not unnecessarily expose personal data or the user’s use of assistive technology.', 'description_sv' => 'Tillgänglighetsfunktioner avslöjar inte i onödan personuppgifter eller användarens användning av hjälpmedelsteknik.'],
            ['number' => '6.1', 'name_en' => 'Audio bandwidth for speech', 'name_sv' => 'Ljudbandbredd för tal', 'description_en' => 'Two-way voice communication supports the required audio bandwidth for accessible speech.', 'description_sv' => 'Tvåvägs röstkommunikation stödjer erforderlig ljudbandbredd för tillgängligt tal.'],
            ['number' => '6.2', 'name_en' => 'Real-time text functionality', 'name_sv' => 'Realtidstextfunktionalitet', 'description_en' => 'Two-way voice communication provides applicable real-time text functionality.', 'description_sv' => 'Tvåvägs röstkommunikation tillhandahåller tillämplig realtidstextfunktionalitet.'],
            ['number' => '6.3', 'name_en' => 'Caller identification', 'name_sv' => 'Uppringaridentifiering', 'description_en' => 'Caller identification information is available in an accessible form where provided.', 'description_sv' => 'Information om uppringaren är tillgänglig i en tillgänglig form när den tillhandahålls.'],
            ['number' => '6.4', 'name_en' => 'Alternatives to voice-based services', 'name_sv' => 'Alternativ till röstbaserade tjänster', 'description_en' => 'An accessible alternative is available where a service relies on voice communication.', 'description_sv' => 'Ett tillgängligt alternativ finns när en tjänst förlitar sig på röstkommunikation.'],
            ['number' => '6.6', 'name_en' => 'Interoperability with emergency services', 'name_sv' => 'Interoperabilitet med räddningstjänster', 'description_en' => 'Applicable ICT supports accessible communication with emergency services.', 'description_sv' => 'Tillämplig IKT stödjer tillgänglig kommunikation med räddningstjänster.'],
            ['number' => '8.1', 'name_en' => 'General hardware requirements', 'name_sv' => 'Allmänna krav på hårdvara', 'description_en' => 'Hardware provides accessible operation and access to required accessibility features.', 'description_sv' => 'Hårdvara erbjuder tillgänglig användning och åtkomst till nödvändiga tillgänglighetsfunktioner.'],
            ['number' => '8.2', 'name_en' => 'Hardware products with speech output', 'name_sv' => 'Hårdvaruprodukter med talutgång', 'description_en' => 'Hardware with speech output provides accessible speech output and controls.', 'description_sv' => 'Hårdvara med talutgång erbjuder tillgänglig talutgång och tillgängliga kontroller.'],
            ['number' => '8.5', 'name_en' => 'Tickets, fare cards, and keycards', 'name_sv' => 'Biljetter, färdbevis och passerkort', 'description_en' => 'Tickets, fare cards, and keycards can be used accessibly where applicable.', 'description_sv' => 'Biljetter, färdbevis och passerkort kan användas tillgängligt när det är tillämpligt.'],
            ['number' => '12.1.1', 'name_en' => 'Accessibility and compatibility features', 'name_sv' => 'Tillgänglighets- och kompatibilitetsfunktioner', 'description_en' => 'Product documentation describes accessibility and compatibility features.', 'description_sv' => 'Produktdokumentation beskriver tillgänglighets- och kompatibilitetsfunktioner.'],
            ['number' => '12.1.2', 'name_en' => 'Accessible documentation', 'name_sv' => 'Tillgänglig dokumentation', 'description_en' => 'Product documentation is provided in an accessible format.', 'description_sv' => 'Produktdokumentation tillhandahålls i ett tillgängligt format.'],
            ['number' => '12.2.2', 'name_en' => 'Information on accessibility and compatibility features', 'name_sv' => 'Information om tillgänglighets- och kompatibilitetsfunktioner', 'description_en' => 'Support information explains the accessibility and compatibility features of the ICT.', 'description_sv' => 'Supportinformation förklarar IKT:s tillgänglighets- och kompatibilitetsfunktioner.'],
            ['number' => '12.2.3', 'name_en' => 'Effective communication', 'name_sv' => 'Effektiv kommunikation', 'description_en' => 'Support channels provide effective communication for users with disabilities.', 'description_sv' => 'Supportkanaler erbjuder effektiv kommunikation för användare med funktionsnedsättning.'],
            ['number' => '12.2.4', 'name_en' => 'Accessible support documentation', 'name_sv' => 'Tillgänglig supportdokumentation', 'description_en' => 'Support documentation is available in an accessible format.', 'description_sv' => 'Supportdokumentation finns i ett tillgängligt format.'],
            ['number' => '13.1.1', 'name_en' => 'Access to relay services', 'name_sv' => 'Åtkomst till förmedlingstjänster', 'description_en' => 'ICT provides accessible access to applicable relay services.', 'description_sv' => 'IKT erbjuder tillgänglig åtkomst till tillämpliga förmedlingstjänster.'],
            ['number' => '13.1.2', 'name_en' => 'Access to emergency services', 'name_sv' => 'Åtkomst till räddningstjänster', 'description_en' => 'ICT provides accessible access to applicable emergency services.', 'description_sv' => 'IKT erbjuder tillgänglig åtkomst till tillämpliga räddningstjänster.'],
            ['number' => '13.2.1', 'name_en' => 'Real-time text emergency communication', 'name_sv' => 'Realtidstext för nödkommunikation', 'description_en' => 'Emergency communication supports applicable real-time text functionality.', 'description_sv' => 'Nödkommunikation stödjer tillämplig realtidstextfunktionalitet.'],
            ['number' => '13.2.2', 'name_en' => 'Caller location for emergency services', 'name_sv' => 'Uppringarens position vid nödsamtal', 'description_en' => 'Applicable emergency communication provides caller location information.', 'description_sv' => 'Tillämplig nödkommunikation tillhandahåller information om uppringarens position.'],
            ['number' => '13.2.3', 'name_en' => 'Video relay for emergency services', 'name_sv' => 'Videoförmedling för räddningstjänster', 'description_en' => 'Applicable emergency communication supports accessible video relay.', 'description_sv' => 'Tillämplig nödkommunikation stödjer tillgänglig videoförmedling.'],
        ];

        $criteria = [
            ...$wcag21Criteria,
            ...$wcag22Criteria,
            ...array_map(fn (array $criterion): array => [
                ...$criterion,
                'standard' => 'EN 301 549',
                'level' => 'N/A',
                'url' => 'https://www.etsi.org/deliver/etsi_en/301500_301599/301549/04.01.01_60/en_301549v040101p.pdf',
            ], $en301549Criteria),
        ];

        foreach ($criteria as $criterion) {
            $criterionModel = WcagSuccessCriterion::firstOrNew(
                [
                    'standard' => $criterion['standard'],
                    'number' => $criterion['number'],
                ],
            );
            $criterionModel->fill($criterion);

            if (! $criterionModel->exists) {
                $criterionModel->id = Str::ulid();
            }

            $criterionModel->save();
        }
    }
}
