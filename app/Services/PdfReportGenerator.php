<?php

namespace App\Services;

use App\Models\AccessibilityReport;
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
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 9,
            'margin_footer' => 9,
            'lang' => 'en',
        ]);

        $mpdf->SetAuthor($this->report->creator->name);
        $mpdf->SetTitle($this->report->title);
        $mpdf->SetSubject('Accessibility Audit Report');
        $mpdf->SetKeywords('WCAG, Accessibility, Audit');

        // Write the HTML content
        $mpdf->WriteHTML($this->report->html_content);

        // Return PDF as binary string
        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    public function download(string $filename = null): string
    {
        if (!$filename) {
            $filename = 'Report_' . $this->report->title . '_' . now()->format('Y-m-d') . '.pdf';
        }

        return $this->generate();
    }
}
