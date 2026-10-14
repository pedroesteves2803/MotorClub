<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    public function svg(string $url, string $darkColor = '#111111', string $lightColor = '#ffffff'): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'drawCircularModules' => false,
            'cssClass' => 'autoverse-qr',
            'moduleValues' => $this->moduleValues($darkColor, $lightColor),
        ]);

        return (new QRCode($options))->render($url);
    }

    public function png(string $url, string $darkColor = '#111111', string $lightColor = '#ffffff'): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 8,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'moduleValues' => $this->moduleValues($darkColor, $lightColor, true),
            'bgColor' => $this->rgb($lightColor),
        ]);

        return (new QRCode($options))->render($url);
    }

    public function sticker(string $url, string $darkColor, string $lightColor, string $label, string $brand): string
    {
        $qr = $this->svg($url, $darkColor, $lightColor);
        preg_match('/viewBox="0 0 ([0-9.]+) ([0-9.]+)"/', $qr, $matches);
        $size = (float) ($matches[1] ?? 29);
        $scale = min(12, 760 / max($size, 1));
        $canvas = $size * $scale + 120;
        $inner = preg_replace('/^.*?<svg[^>]*>/s', '', $qr);
        $inner = preg_replace('/<\/svg>\s*$/s', '', (string) $inner);
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeBrand = htmlspecialchars($brand, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$s" height="%1$s" viewBox="0 0 %1$s %1$s"><style>.dark{fill:%2$s}.light{fill:%3$s}text{font-family:Arial,sans-serif}</style><rect width="100%%" height="100%%" rx="28" fill="%3$s"/><text x="60" y="58" font-size="28" font-weight="700" fill="%2$s">%4$s</text><text x="60" y="92" font-size="17" fill="%2$s" opacity=".72">%5$s</text><g transform="translate(60 120) scale(%6$s)">%7$s</g></svg>',
            $canvas,
            e($darkColor),
            e($lightColor),
            $safeLabel,
            $safeBrand,
            $scale,
            $inner,
        );
    }

    private function moduleValues(string $darkColor, string $lightColor, bool $png = false): array
    {
        $dark = $png ? $this->rgb($darkColor) : $darkColor;
        $light = $png ? $this->rgb($lightColor) : $lightColor;

        return array_fill_keys([
            QRMatrix::M_DARKMODULE, QRMatrix::M_DATA_DARK, QRMatrix::M_FINDER_DARK,
            QRMatrix::M_ALIGNMENT_DARK, QRMatrix::M_TIMING_DARK, QRMatrix::M_FORMAT_DARK,
            QRMatrix::M_VERSION_DARK, QRMatrix::M_FINDER_DOT, QRMatrix::M_SEPARATOR_DARK,
        ], $dark) + array_fill_keys([
            QRMatrix::M_DATA, QRMatrix::M_FINDER, QRMatrix::M_SEPARATOR, QRMatrix::M_ALIGNMENT,
            QRMatrix::M_TIMING, QRMatrix::M_FORMAT, QRMatrix::M_VERSION, QRMatrix::M_QUIETZONE,
            QRMatrix::M_DARKMODULE_LIGHT, QRMatrix::M_FINDER_DOT_LIGHT, QRMatrix::M_QUIETZONE_DARK,
        ], $light);
    }

    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
