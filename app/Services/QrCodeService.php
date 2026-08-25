<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class QrCodeService
{
    /**
     * Make a QR code PNG for an asset and save it to the public disk.
     * Needs the `qrencode` CLI tool installed (sudo apt-get install qrencode).
     *
     * @return string|null Path to the PNG, or null on failure.
     */
    public function generateForAsset(string $assetCode, string $payload): ?string
    {
        Storage::disk('public')->makeDirectory('qrcodes');

        $relativePath = "qrcodes/{$assetCode}.png";
        $absolutePath = Storage::disk('public')->path($relativePath);

        $process = new Process([
            'qrencode',
            '-o', $absolutePath,
            '-s', '8',       // pixel scale
            '-m', '2',       // margin
            '-l', 'M',       // error correction
            $payload,
        ]);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
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
