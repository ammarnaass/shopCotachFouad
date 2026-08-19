<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates shipping-label PDFs through mPDF.
 *
 * mPDF is used for labels because, unlike DomPDF, it fully supports Arabic
 * letter shaping/joining and the BiDi algorithm, and ships with a built-in
 * Code 128 barcode generator. Invoices and other documents stay on DomPDF.
 *
 * The generated file is returned as an attachment so the browser downloads it
 * directly, with no print dialog and no user interaction.
 */
class LabelPdfService
{
    /**
     * Render HTML to a PDF and return it as a silent download response.
     *
     * @param  string  $html      Fully-rendered label HTML.
     * @param  string  $filename  Download file name (e.g. "label-SH1234.pdf").
     * @param  string  $dir       Base text direction: 'rtl' or 'ltr'.
     * @param  string  $format    mPDF paper format (e.g. 'A5-L' for A5 landscape).
     */
    public function download(string $html, string $filename, string $dir = 'rtl', string $format = 'A5-L'): Response
    {
        $tmp = storage_path('app/mpdf');
        if (! is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,                        // A5 landscape (same size as the old DomPDF labels)
            'tempDir' => $tmp,
            'default_font' => 'dejavusanscondensed',    // bundled font with Arabic glyphs; mPDF shapes/joins automatically
        ]);

        if ($dir === 'rtl') {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->WriteHTML($html);

        $pdf = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"', // silent download (no print dialog)
        ]);
    }
}
