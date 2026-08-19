<?php

namespace App\Services\Documents;

use ArPHP\I18N\Arabic;

class ArabicPdfHtmlProcessor
{
    private static ?Arabic $arabic = null;

    /**
     * Prepare HTML for DomPDF by shaping Arabic text segments into visual glyphs.
     * DomPDF does not support RTL/bidi natively; ar-php fixes reversed/disconnected Arabic.
     */
    public static function process(string $html): string
    {
        if (! preg_match('/\p{Arabic}/u', $html)) {
            return $html;
        }

        $arabic = self::instance();
        $positions = $arabic->arIdentify($html);

        if (empty($positions)) {
            return $html;
        }

        for ($i = count($positions) - 1; $i >= 0; $i -= 2) {
            $start = $positions[$i - 1];
            $length = $positions[$i] - $start;
            $segment = substr($html, $start, $length);
            $processed = $arabic->utf8Glyphs($segment, 150, false, true);
            $html = substr_replace($html, $processed, $start, $length);
        }

        return $html;
    }

    private static function instance(): Arabic
    {
        return self::$arabic ??= new Arabic();
    }
}
