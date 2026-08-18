<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class QrCodeService
{
    /**
     * Generate a QR code PNG for the given asset and store it on the public disk.
     *
     * Uses the `qrencode` CLI tool rather than a Composer package, since this
     * app avoids adding Packagist dependencies beyond the Laravel skeleton.
     * Install it with: sudo apt-get install qrencode
     *
     * @return string|null Relative path (on the "public" disk) to the generated PNG, or null on failure.
     */
    public function generateForAsset(string $assetCode, string $payload): ?string
    {
        Storage::disk('public')->makeDirectory('qrcodes');

        $relativePath = "qrcodes/{$assetCode}.png";
        $absolutePath = Storage::disk('public')->path($relativePath);

        $process = new Process([
            'qrencode',
            '-o', $absolutePath,
            '-s', '8',       // module size (pixel scale)
            '-m', '2',       // margin
            '-l', 'M',       // error correction level
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
