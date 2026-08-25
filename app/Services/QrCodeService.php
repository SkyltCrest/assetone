<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    /**
     * Render a QR code SVG for the given payload.
     * Generated on the fly (not stored to disk) so it survives container
     * restarts/redeploys on hosts with ephemeral storage, like Render.
     */
    public function svgFor(string $payload): string
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: $payload,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
        ))->build();

        return $result->getString();
    }
}
