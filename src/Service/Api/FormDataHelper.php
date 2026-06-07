<?php

namespace App\Service\Api;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class FormDataHelper
{
    public static function getString(Request $request, string $key, ?string $default = null): ?string
    {
        $value = $request->request->get($key);
        if (!is_string($value) || trim($value) === '') {
            return $default;
        }

        return trim($value);
    }

    public static function resolveIriId(?string $iri): ?int
    {
        if ($iri === null || $iri === '') {
            return null;
        }

        if (preg_match('#/(\d+)$#', $iri, $matches)) {
            return (int) $matches[1];
        }

        if (ctype_digit($iri)) {
            return (int) $iri;
        }

        return null;
    }

    /**
     * @return UploadedFile[]
     */
    public static function getUploadedFiles(Request $request, string $key): array
    {
        $files = $request->files->all();
        $direct = $files[$key] ?? null;
        if ($direct instanceof UploadedFile) {
            return [$direct];
        }
        if (is_array($direct)) {
            return array_values(array_filter($direct, static fn ($f) => $f instanceof UploadedFile));
        }

        $bracketKey = $key . '[]';
        $bracket = $files[$bracketKey] ?? null;
        if ($bracket instanceof UploadedFile) {
            return [$bracket];
        }
        if (is_array($bracket)) {
            return array_values(array_filter($bracket, static fn ($f) => $f instanceof UploadedFile));
        }

        return [];
    }
}
