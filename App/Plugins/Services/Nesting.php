<?php

namespace App\Plugins\Services;

use const PREG_SPLIT_NO_EMPTY;
use const PREG_SPLIT_DELIM_CAPTURE;

class Nesting
{
    public function expandNestedCss(string $css): string
    {
        // SANEAR CSS
        $css = $this->sanitizeCss($css);

        // TOKENIZAR
        $tokens = preg_split('/([{}])/', $css, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $stack = [];
        $isMedia = [];
        $output = "";

        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = trim($tokens[$i]);

            // BLOQUE “{”
            if ($token === "{") {
                $selector = trim($tokens[$i - 1] ?? "");

                // Es @media
                if (preg_match('/^@media/i', $selector)) {
                    $stack[] = $selector;
                    $isMedia[] = true;
                    $output .= $selector . " {\n";
                } else {
                    // Selector normal
                    $stack[] = $selector;
                    $isMedia[] = false;
                    $output .= $selector . " {\n";
                }
                continue;
            }

            // BLOQUE “}”
            if ($token === "}") {
                array_pop($stack);
                array_pop($isMedia);
                $output .= "}\n\n";
                continue;
            }

            // SI VIENE SELECTOR SEGUIDO DE "{"
            $next = $tokens[$i + 1] ?? "";

            if ($next === "{") {
                $child = $token;
                $parent = $this->getParentSelector($stack);
                $inMedia = end($isMedia);

                // Caso: nesting real con “&”
                if (strpos($child, "&") !== false) {
                    $child = str_replace("&", $parent, $child);

                // Caso: selector absoluto, no concatenar
                } elseif ($this->isAbsoluteSelector($child, $parent)) {
                    // no tocar

                // Caso: nesting normal fuera de media
                } elseif (!$inMedia) {
                    $child = $this->mergeSelectors($parent, $child);

                // Caso: dentro de media, pero sin &, no concatenar
                }

                $output .= $child . " {\n";
                continue;
            }

            // ES UNA PROPIEDAD
            if (!empty($stack)) {
                $output .= $token . "\n";
            }
        }

        return trim($output);
    }

    private function sanitizeCss(string $css): string
    {
        $lines = explode("\n", $css);
        $clean = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Reparar casos tipo: ".sticky-top position:"
            if (preg_match('/^[.#][A-Za-z0-9\-_ ]+ [A-Za-z\-]+:/', $line)) {
                $line = preg_replace('/^[.#][A-Za-z0-9\-_ ]+ /', '', $line);
            }

            // Reparar "@media (...) .selector {" → separarlo
            if (preg_match('/^@media[^{]+ [.#]/', $line)) {
                $line = preg_replace('/(@media[^{]+) (.+)/', "$1 {\n$2", $line);
            }

            $clean[] = $line;
        }

        return implode("\n", $clean);
    }

    private function getParentSelector(array $stack): string
    {
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            if (!preg_match('/^@media/i', $stack[$i])) {
                return trim($stack[$i]);
            }
        }
        return "";
    }

    private function isAbsoluteSelector(string $child, string $parent): bool
    {
        // Child contiene el parent como palabra → no mergear
        $parentEsc = preg_quote($parent, "/");
        if (preg_match('/(^|[\s>+~])' . $parentEsc . '($|[\s>+~])/', $child)) {
            return true;
        }

        return false;
    }

    private function mergeSelectors(string $parent, string $child): string
    {
        $out = [];
        $parents = array_map("trim", explode(",", $parent));
        $children = array_map("trim", explode(",", $child));

        foreach ($parents as $p) {
            foreach ($children as $c) {
                $out[] = trim("$p $c");
            }
        }

        return implode(", ", $out);
    }
}
