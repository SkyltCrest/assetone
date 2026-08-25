<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class QrCodeService
{
    /**
     * Make a QR code SVG for an asset and save it to the public disk.
     * Pure PHP (no GD/Imagick or system tools needed), so it works the same on Windows and Linux.
     *
     * @return string|null Path to the SVG, or null on failure.
     */
    public function generateForAsset(string $assetCode, string $payload): ?string
    {
        Storage::disk('public')->makeDirectory('qrcodes');

        $relativePath = "qrcodes/{$assetCode}.svg";
        $absolutePath = Storage::disk('public')->path($relativePath);

        try {
            $result = (new Builder(
                writer: new SvgWriter(),
                data: $payload,
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 300,
                margin: 10,
            ))->build();

            $result->saveToFile($absolutePath);
        } catch (Throwable $e) {
            Log::warning("QR code generation failed for asset {$assetCode}: ".$e->getMessage());

            return null;
        }

        return $relativePath;
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
