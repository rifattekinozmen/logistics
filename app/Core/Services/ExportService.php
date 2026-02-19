<?php

namespace App\Core\Services;

use Illuminate\Http\Response;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * CSV olarak dışa aktar.
     *
     * @param  array<int, string>  $headers  Başlık satırı
     * @param  array<int, array<int, string|null>>  $rows  Veri satırları
     */
    public function csv(array $headers, array $rows, string $filename): StreamedResponse
    {
        $filename = $this->sanitizeFilename($filename, 'csv');

        $headersResponse = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($headers, $rows): void {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headersResponse);
    }

    /**
     * XML olarak dışa aktar.
     *
     * @param  array<int, string>  $headers  Öğe adları (XML uyumlu tag)
     * @param  array<int, array<int, string|null>>  $rows  Veri satırları
     */
    public function xml(
        array $headers,
        array $rows,
        string $filename,
        string $rootElement = 'export',
        string $rowElement = 'row'
    ): Response {
        $filename = $this->sanitizeFilename($filename, 'xml');
        $tagNames = array_map(fn (string $h) => $this->xmlTagName($h), $headers);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.$rootElement.'/>');
        $xml->addAttribute('encoding', 'UTF-8');

        foreach ($rows as $row) {
            $child = $xml->addChild($rowElement);
            foreach ($tagNames as $i => $tag) {
                $value = $row[$i] ?? '';
                $child->addChild($tag, htmlspecialchars((string) $value, ENT_XML1, 'UTF-8'));
            }
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return response($dom->saveXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function sanitizeFilename(string $name, string $ext): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\p{L}]/u', '_', $name);

        return ($name ?: 'export').'_'.now()->format('Y-m-d_His').'.'.$ext;
    }

    protected function xmlTagName(string $label): string
    {
        $tag = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $label);
        $tag = trim($tag, '_');
        if ($tag === '' || is_numeric($tag[0])) {
            $tag = 'field_'.$tag;
        }

        return strtolower(substr($tag, 0, 50)) ?: 'field';
    }
}
