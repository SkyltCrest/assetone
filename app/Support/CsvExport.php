<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a list of rows to the browser as a CSV download.
 */
class CsvExport
{
    /**
     * @param  string             $name      File name without the extension.
     * @param  list<string>       $headings  Column titles.
     * @param  iterable<int, list<scalar|null>>  $rows
     */
    public static function download(string $name, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // lets Excel read UTF-8 correctly
            fputcsv($out, $headings);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $name.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }
}
