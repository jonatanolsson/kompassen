<?php

namespace App\Services;

use App\Models\AccessibilityReport;
use DOMDocument;
use Illuminate\Validation\ValidationException;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfReportGenerator
{
    public function __construct(
        private AccessibilityReport $report,
    ) {}

    public function generate(): string
    {
        $mpdf = new Mpdf([
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 9,
            'margin_footer' => 9,
            'lang' => app()->getLocale(),
        ]);
        $mpdf->fonttrans['verdana'] = 'dejavusans';

        $mpdf->SetAuthor($this->report->document_metadata['creator'] ?? $this->report->creator?->name ?? config('app.name'));
        $mpdf->SetTitle($this->report->title);
        $mpdf->SetSubject('Accessibility Audit Report');
        $mpdf->SetKeywords('WCAG, Accessibility, Audit');
        $mpdf->SetHTMLFooter('<div style="text-align: right; color: #52525b; font-size: 9pt;">'
            .e(__('Page :page of :pages', ['page' => '{PAGENO}', 'pages' => '{nbpg}']))
            .'</div>');

        // Write the HTML content
        $html = preg_replace_callback(
            '/data:image\/svg\+xml;base64,([A-Za-z0-9+\/=]+)/',
            function (array $match): string {
                $svg = base64_decode($match[1], true);

                if ($svg === false) {
                    throw ValidationException::withMessages(['report' => __('A saved report diagram could not be read for PDF export.')]);
                }

                $document = new DOMDocument;
                $document->loadXML((new IssueDiagramSvg)->sanitize($svg, 'report'));

                foreach (['text', 'tspan'] as $tag) {
                    foreach ($document->getElementsByTagNameNS(IssueDiagramSvg::SVG_NAMESPACE, $tag) as $element) {
                        $element->setAttribute('font-family', 'DejaVu Sans');
                    }
                }

                return 'data:image/svg+xml;base64,'.base64_encode(
                    $document->saveXML($document->documentElement)
                    ?: throw new \RuntimeException('Could not prepare report diagram font for PDF export.')
                );
            },
            $this->report->html_content,
        );

        $mpdf->WriteHTML($html ?? throw new \RuntimeException('Could not prepare report diagrams for PDF export.'));

        // Return PDF as binary string
        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
