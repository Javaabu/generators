<?php

namespace Javaabu\Generators\Support;

use RuntimeException;

/**
 * Small, careful edits of existing PHP files: add a use statement, add
 * statements to a method, add entries to a returned array.
 */
class PhpFile
{
    public static function addImport(string $code, string $class): string
    {
        $class = ltrim($class, '\\');

        if (preg_match('/^use ' . preg_quote($class, '/') . ';$/m', $code)) {
            return $code;
        }

        $statement = "use {$class};";

        // after the last use statement before the class
        if (preg_match_all('/^use [^;(]+;$/m', $code, $matches, PREG_OFFSET_CAPTURE)) {
            $uses = array_map(fn (array $match) => $match[0], $matches[0]);
            $uses[] = $statement;

            usort($uses, fn (string $a, string $b) => strcasecmp(rtrim($a, ';'), rtrim($b, ';')));

            $first = $matches[0][0][1];
            $last = end($matches[0]);
            $end = $last[1] + strlen($last[0]);

            return substr($code, 0, $first) . implode("\n", $uses) . substr($code, $end);
        }

        if (preg_match('/^namespace [^;]+;$/m', $code, $match, PREG_OFFSET_CAPTURE)) {
            $end = $match[0][1] + strlen($match[0][0]);

            return substr($code, 0, $end) . "\n\n{$statement}" . substr($code, $end);
        }

        return preg_replace('/^<\?php\s*/', "<?php\n\n{$statement}\n\n", $code, 1);
    }

    /**
     * Add statements at the end of a method's body, unless they're already there.
     */
    public static function appendToMethod(string $code, string $method, string $statements): string
    {
        if (! preg_match('/function ' . preg_quote($method, '/') . '\s*\([^)]*\)[^{]*\{/', $code, $match, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException("The method {$method}() was not found.");
        }

        $open = $match[0][1] + strlen($match[0][0]) - 1;
        $close = static::matchingBrace($code, $open);

        // indentation of the method
        $line_start = strrpos(substr($code, 0, $match[0][1]), "\n");
        $indent = substr($code, $line_start + 1, $match[0][1] - $line_start - 1);
        $indent = preg_replace('/\S.*$/', '', $indent);
        $body_indent = $indent . '    ';

        $body = substr($code, $open + 1, $close - $open - 1);
        $trimmed = rtrim($body);

        $statements = implode("\n", array_map(
            fn (string $line) => $line === '' ? '' : $body_indent . $line,
            explode("\n", trim($statements, "\n"))
        ));

        if (str_contains($body, trim($statements))) {
            return $code;
        }

        $new_body = trim($trimmed) === ''
            ? "\n{$statements}\n{$indent}"
            : "{$trimmed}\n\n{$statements}\n{$indent}";

        return substr($code, 0, $open + 1) . $new_body . substr($code, $close);
    }

    /**
     * Add code before a marker comment, or at the end of a closure group
     * that contains the marker's text.
     */
    public static function insertBeforeMarker(string $code, string $marker, string $content): string
    {
        $position = strpos($code, $marker);

        if ($position === false) {
            throw new RuntimeException("The marker [{$marker}] was not found.");
        }

        $line_start = strrpos(substr($code, 0, $position), "\n") + 1;
        $indent = substr($code, $line_start, $position - $line_start);

        $content = implode("\n", array_map(
            fn (string $line) => $line === '' ? '' : $indent . $line,
            explode("\n", trim($content, "\n"))
        ));

        if (str_contains($code, trim($content))) {
            return $code;
        }

        return substr($code, 0, $line_start) . $content . "\n\n" . substr($code, $line_start);
    }

    /**
     * Add an entry to an array under a key, e.g. 'customer' => [...] to 'guards'
     * in config/auth.php, unless the array has the entry's key already.
     */
    public static function addArrayEntry(string $code, string $array_key, string $entry_key, string $entry): string
    {
        if (! preg_match("/(['\"]){$array_key}\\1\\s*=>\\s*\\[/", $code, $match, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException("The [{$array_key}] array was not found.");
        }

        $open = $match[0][1] + strlen($match[0][0]) - 1;
        $close = static::matchingBracket($code, $open);

        $body = substr($code, $open + 1, $close - $open - 1);

        if (preg_match("/(['\"])" . preg_quote($entry_key, '/') . '\\1\\s*=>/', $body)) {
            return $code;
        }

        $line_start = strrpos(substr($code, 0, $match[0][1]), "\n") + 1;
        $indent = preg_replace('/\S.*$/s', '', substr($code, $line_start, $match[0][1] - $line_start));
        $item_indent = $indent . '    ';

        $entry = implode("\n", array_map(
            fn (string $line) => $line === '' ? '' : $item_indent . $line,
            explode("\n", trim($entry))
        ));

        $trimmed = rtrim($body);

        $new_body = trim($trimmed) === ''
            ? "\n{$entry}\n{$indent}"
            : $trimmed . (str_ends_with($trimmed, ',') ? '' : ',') . "\n\n{$entry}\n{$indent}";

        return substr($code, 0, $open + 1) . $new_body . substr($code, $close);
    }

    protected static function matchingBracket(string $code, int $open): int
    {
        $depth = 0;
        $length = strlen($code);

        for ($i = $open; $i < $length; $i++) {
            $char = $code[$i];

            if ($char === '\'' || $char === '"') {
                $i = static::skipString($code, $i);

                continue;
            }

            if ($char === '[') {
                $depth++;
            }

            if ($char === ']') {
                $depth--;

                if ($depth === 0) {
                    return $i;
                }
            }
        }

        throw new RuntimeException('Unbalanced brackets.');
    }

    protected static function matchingBrace(string $code, int $open): int
    {
        $depth = 0;
        $length = strlen($code);

        for ($i = $open; $i < $length; $i++) {
            $char = $code[$i];

            if ($char === '\'' || $char === '"') {
                $i = static::skipString($code, $i);

                continue;
            }

            if ($char === '{') {
                $depth++;
            }

            if ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    return $i;
                }
            }
        }

        throw new RuntimeException('Unbalanced braces.');
    }

    protected static function skipString(string $code, int $start): int
    {
        $quote = $code[$start];
        $length = strlen($code);

        for ($i = $start + 1; $i < $length; $i++) {
            if ($code[$i] === '\\') {
                $i++;

                continue;
            }

            if ($code[$i] === $quote) {
                return $i;
            }
        }

        return $length;
    }
}
