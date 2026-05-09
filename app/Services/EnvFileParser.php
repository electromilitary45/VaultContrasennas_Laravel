<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Parser para archivos .env.
 * Convierte texto raw en líneas numeradas e identifica secciones (comentarios #).
 */
class EnvFileParser
{
    /**
     * Parsea el contenido raw de un .env en líneas numeradas.
     *
     * @param string $raw Contenido del archivo .env
     * @return array<int, array{line_number: int, content: string, is_section_header: bool}>
     */
    public function parse(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $result = [];
        $lineNumber = 1;

        foreach ($lines as $line) {
            $content = $line;
            $isSectionHeader = str_starts_with(trim($content), '#');
            $result[] = [
                'line_number' => $lineNumber,
                'content' => $content,
                'is_section_header' => $isSectionHeader,
            ];
            $lineNumber++;
        }

        return $result;
    }

    /**
     * Obtiene los números de línea que pertenecen a una sección.
     * Una sección va desde un comentario # hasta el siguiente # o fin.
     *
     * @param array<int, array{line_number: int, content: string, is_section_header: bool}> $lines
     * @param int $sectionStartLine Número de línea del comentario que inicia la sección
     * @return list<int>
     */
    public function getSectionLineNumbers(array $lines, int $sectionStartLine): array
    {
        $lineNumbers = [];
        $inSection = false;

        foreach ($lines as $line) {
            $num = $line['line_number'];
            if ($num === $sectionStartLine) {
                $inSection = true;
            }
            if ($inSection) {
                $lineNumbers[] = $num;
                if ($num !== $sectionStartLine && $line['is_section_header']) {
                    break;
                }
            }
        }

        return $lineNumbers;
    }

    /**
     * Reconstruye el texto raw a partir de las líneas visibles.
     *
     * @param array<int, array{line_number: int, content: string, is_section_header: bool}> $lines
     * @param list<int> $visibleLineNumbers Líneas a incluir
     * @return string
     */
    public function buildRawFromLines(array $lines, array $visibleLineNumbers): string
    {
        $visibleSet = array_flip($visibleLineNumbers);
        $output = [];

        foreach ($lines as $line) {
            if (isset($visibleSet[$line['line_number']])) {
                $output[] = $line['content'];
            }
        }

        return implode("\n", $output);
    }

    /**
     * Filtra líneas según las reglas de visibilidad para un usuario.
     * visibility_rules: { "user:123" => [1, 2, [5, 10]], "group:5" => [[1, 20]] }
     * Un número = línea única. [a, b] = rango inclusive.
     *
     * @param array<int, array{line_number: int, content: string, is_section_header: bool}> $lines
     * @param array<string, array<int|array{0: int, 1: int}>> $visibilityRules
     * @param list<int> $userIds IDs de usuario
     * @param list<int> $groupIds IDs de grupos del usuario
     * @return list<int>
     */
    public function getVisibleLineNumbers(
        array $lines,
        array $visibilityRules,
        array $userIds,
        array $groupIds
    ): array {
        $visible = [];

        foreach ($visibilityRules as $key => $ranges) {
            $isUser = str_starts_with($key, 'user:');
            $isGroup = str_starts_with($key, 'group:');

            $applies = false;
            if ($isUser) {
                $id = (int) substr($key, 5);
                $applies = in_array($id, $userIds, true);
            } elseif ($isGroup) {
                $id = (int) substr($key, 6);
                $applies = in_array($id, $groupIds, true);
            }

            if (!$applies) {
                continue;
            }

            foreach ($ranges as $range) {
                if (is_int($range)) {
                    $visible[] = $range;
                } elseif (is_array($range) && count($range) >= 2) {
                    $start = (int) $range[0];
                    $end = (int) $range[1];
                    for ($n = $start; $n <= $end; $n++) {
                        $visible[] = $n;
                    }
                }
            }
        }

        return array_values(array_unique($visible));
    }
}
