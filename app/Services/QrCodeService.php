<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    public function svg(string $url, string $darkColor = '#111111'): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'drawCircularModules' => false,
            'cssClass' => 'autoverse-qr',
        ]);

        return (new QRCode($options))->render($url);
    }

    public function png(string $url): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'scale' => 8,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($url);
    }
}
