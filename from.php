<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        return from\v($value, $array, $lot);
    }
}

namespace x\y_a_m_l\from {
    \define(__NAMESPACE__ . "\\str", '"(?>\\.|[^"])*"|\'(?>\'\'|[^\'])*\'');
    // Remove comment(s)
    function c(string $value): string {
        $out = "";
        if (false !== \strpos('>|', $value[0]) && \preg_match('/^([>|][+-]?\d*)[ \t]*(#[^\n]*)?(\n(\n|[ \t]+[^\n]*)*)?/', $value, $m)) {
            $out .= $m[1] . ($m[3] ?? "");
            $value = \substr($value, \strlen($m[0]));
        }
        while (false !== ($v = \strpbrk($value, '#"\'' . "\n"))) {
            if ("" !== ($r = \substr($value, 0, \strlen($value) - \strlen($v)))) {
                $out .= $r;
                $value = \substr($value, \strlen($r));
            }
            if (0 === \strpos($v, '#')) {
                $v = false !== ($n = \strpos($v, "\n")) ? \substr($v, 0, $n) : $v;
                $value = \substr($value, \strlen($v));
                continue;
            }
            if (false !== \strpos('"\'', $v[0]) && \preg_match('/^' . str . '[^\n#]*/', $v, $m)) {
                $out .= \trim($m[0]);
                $value = \substr($value, \strlen($m[0]));
                continue;
            }
            if (0 === \strpos($v, "\n")) {
                $out .= "\n";
                $value = \substr($value, 1);
                continue;
            }
            $out .= $v;
            $value = \substr($value, \strlen($v));
        }
        if ("" !== $value) {
            $out .= $value;
            $value = "";
        }
        return $out;
    }
    // <https://yaml-multiline.info>
    function f(string $value, $dent = true): string {
        $content = "";
        $test = 0;
        foreach (\explode("\n", $value) as $k => &$v) {
            if ("" === $v) {
                $content .= "\n";
                continue;
            }
            if ($dent && $test !== ($t = \strspn($v, " \t"))) {
                $test = $t;
                $content .= "\n" . $v;
                continue;
            }
            $content .= ($k > 0 && "\n" !== \substr($content, -1) ? ' ' : "") . ($k > 0 ? \ltrim($v) : $v);
        }
        return $content;
    }
    function r(string $value): string {
        $out = ($a = '[' === $value[0]) ? '- ' : "";
        $value = \trim(\trim(\substr($value, 1, -1)), ',');
        foreach (\preg_split('/(\[(?>(?R)|[^][])*\]|\{(?>(?R)|[^{}])*\}|(?>' . str . '|[^,:]+)\s*:\s*|' . str . '|,)/', $value, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
            if ("" === ($v = \trim($v))) {
                continue;
            }
            if (':' === \substr($v, -1)) {
                $out .= $v . ' ';
                continue;
            }
            if (',' === $v) {
                $out .= "\n" . ($a ? '- ' : "");
                continue;
            }
            if ('[' === $v[0] && ']' === \substr($v, -1)) {
                if ($o = ': ' === \substr($out, -2)) {
                    $out = \substr($out, 0, -1) . "\n";
                }
                $v = r($v);
                $out .= $o ? $v : \strtr($v, [
                    "\n" => "\n  "
                ]);
                continue;
            }
            if ('{' === $v[0] && '}' === \substr($v, -1)) {
                $out .= "\n " . \strtr(r($v), [
                    "\n" => "\n "
                ]);
                continue;
            }
            $out .= $v;
            // Fix case for value-less object flow like `{a,b,c}`
            $test = false !== ($v = \strrchr($out, "\n")) ? \substr($v, 1) : $out;
            if (0 !== \strpos($test, '- ') && false === \strpos(\preg_replace('/' . str . '/', "", $test), ': ')) {
                $out .= ':';
            }
        }
        return $out;
    }
    // <https://yaml.org/type>
    function t($value, $array, $lot, $tag) {
        if (isset($lot[$tag]) && \is_callable($lot[$tag])) {
            return \call_user_func($lot[$tag], $value, $array, $lot);
        }
        if (0 === \strpos($tag, '!!')) {
            $tag = \substr($tag, 2);
            if ('binary' === $tag) {
                return \base64_decode(\preg_replace('/\s+/', "", \trim($value ?? 'AA==')));
            }
            if ('bool' === $tag) {
                return (bool) $value;
            }
            if ('float' === $tag) {
                return (float) $value;
            }
            if ('int' === $tag) {
                return (int) $value;
            }
            if ('map' === $tag) {
                return (object) $value;
            }
            if ('null' === $tag) {
                return null;
            }
            if ('seq' === $tag) {
                return \array_values((array) $value);
            }
            if ('str' === $tag) {
                return (string) $value;
            }
            if ('timestamp' === $tag) {
                return new \DateTime((string) $value);
            }
        }
        return $value;
    }
    function v(?string $value, $array = false, array &$lot = []) {
        if ("" === ($value = \trim($raw = $value ?? ""))) {
            return null;
        }
        // Normalize line break(s)
        $value = \strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        if ("" === ($value = \trim(c($value)))) {
            return null;
        }
        if (\array_key_exists($var = \strtolower($value), $vars = [
            "''" => "",
            '""' => "",
            '+.inf' => \INF,
            '+.nan' => \NAN,
            '-.inf' => -\INF,
            '-.nan' => -\NAN,
            '.inf' => \INF,
            '.nan' => \NAN,
            '[]' => [],
            'false' => false,
            'null' => null,
            'true' => true,
            '{}' => $array ? [] : (object) [],
            '~' => null
        ])) {
            return $vars[$var];
        }
        if ('"' === $value[0] && '"' === \substr($value, -1)) {
            return \stripcslashes(f(\strtr(\substr($value, 1, -1), [
                "\\\n" => ""
            ])));
        }
        if ("'" === $value[0] && "'" === \substr($value, -1)) {
            return f(\strtr(\substr($value, 1, -1), [
                "''" => "'"
            ]), false);
        }
        // Fold-style or literal-style value
        if (false !== \strpos('>|', $value[0])) {
            [$rule, $content] = \array_replace(["", ""], \explode("\n", c(\ltrim($raw)), 2));
            $dent = \strspn(\trim($content, "\n"), ' ');
            $content = \substr(\strtr("\n" . $content, [
                "\n" . \str_repeat(' ', $dent) => "\n"
            ]), 1);
            $d = 0;
            if ($cut = $rule[1] ?? "") {
                // `>+1`
                if (false !== \strpos('+-', $cut)) {
                    $dent -= ($d = (int) \substr($rule, 2));
                // `>1`
                } else if (\is_numeric($cut)) {
                    $cut = "";
                    $dent -= ($d = (int) \substr($rule, 1));
                }
            // `>`
            } else {
                $cut = "";
                $dent = 0;
            }
            if ("" !== $cut && false === \strpos('+-', $cut)) {
                return $raw;
            }
            if ('+' !== $cut) {
                $content = \rtrim($content) . ("" === $cut ? "\n" : "");
            }
            if ('>' === $rule[0]) {
                $content = f($content);
            }
            if ($dent < 0) {
                // throw new \Exception('https://yaml.org/spec/1.2.2#8111-block-indentation-indicator');
                return null;
            }
            $v = $d > 0 ? \str_repeat(' ', $dent) : "";
            return \substr(\strtr(\strtr("\n" . $content, [
                "\n" => "\n" . $v
            ]), [
                "\n" . $v . "\n" => "\n\n"
            ]), 1);
        }
        if ('[' === $value[0] && ']' === \substr($value, -1) || '{' === $value[0] && '}' === \substr($value, -1)) {
            return v(r($value), $array, $lot);
        }
        // A tag
        if ('!' === $value[0]) {
            [$tag, $content] = \array_replace(["", ""], \preg_split('/\s+/', $value, 2, \PREG_SPLIT_NO_EMPTY));
            $value = v($content, $array, $lot);
            if ('!!str' === $tag && !isset($lot[$tag]) && $value instanceof \DateTimeInterface) {
                return $content;
            }
            return t($value, $array, $lot, $tag);
        }
        // <https://yaml.org/spec/1.2.2#692-node-anchors>
        if (false !== \strpos('&*', $value[0]) && \preg_match('/^([&*])([^\s,\[\]{}]+)(\s+|$)/', $value, $m)) {
            $key = '&' . $m[2];
            if ('&' === $m[1]) {
                return ($lot[$key] = v(\substr($value, \strlen($m[0])), $array, $lot));
            }
            return $lot[$key] ?? null;
        }
        // List-style value
        if ('-' === $value[0] && \strlen($value) > 2 && false !== \strpos(" \n\t", $value[1])) {
            $out = [];
            foreach (\preg_split('/\n-[ \n\t]/', \substr($value, 2)) as $v) {
                if (0 === \strpos($v, '- ')) {
                    $v = \strtr($v, [
                        "\n  " => "\n"
                    ]);
                } else {
                    $v = \trim($v);
                }
                $out[] = v($v, $array, $lot);
            }
            return $out;
        }
        if (\strlen($value) > 2 && '0' === $value[0]) {
            // Hex
            if (\preg_match('/^0x[a-f\d]+$/i', $value)) {
                return \hexdec($value);
            }
            // Octal
            if (\preg_match('/^0o?[0-7]+$/i', $value)) {
                if (false !== \strpos('Oo', $value[1])) {
                    // PHP < 8.1
                    $value = \substr($value, 2);
                }
                return \octdec($value);
            }
        }
        // Exponent
        if (\preg_match('/^[+-]?\d*[.]?\d+e[+-]?\d+$/i', $value)) {
            return (float) $value;
        }
        if (\is_numeric($value)) {
            return false !== \strpos($value, '.') ? (float) $value : (int) $value;
        }
        // <https://yaml.org/type/timestamp.html>
        if (\is_numeric($value[0]) && \preg_match('/^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])((t|[ \t]+)([0-1]\d|2[0-4]):([0-5]\d|60)(:([0-5]\d|60)([.]\d+)?)?([ \t]*[+-]([0-1]\d|2[0-4]):([0-5]\d|60)(:([0-5]\d|60)([.]\d+)?)?|z)?)?$/i', $value)) {
            return new \DateTime($value);
        }
        if (false === \strpos($value, ":\n") && false === \strpos($value, ":\t") && false === \strpos($value, ': ') && ':' !== \substr($value, -1)) {
            return f($value, false);
        }
        $block = -1;
        $blocks = [];
        $rows = \explode("\n", $value);
        foreach ($rows as $row) {
            $dent = \strspn($row, ' ');
            $current = $dent > 0 ? \substr($row, $dent) : $row;
            if ($prev = $blocks[$block] ?? 0) {
                // A blank line
                if ("" === $current) {
                    $blocks[$block] .= "\n";
                    continue;
                }
                if (false !== \strpos($prev, '[') && ']' === $current || false !== \strpos($prev, '{') && '}' === $current) {
                    $blocks[$block] .= "\n" . $current;
                    continue;
                }
                // A list
                if ('-' === $current) {
                    $blocks[$block] .= "\n- ";
                    continue;
                }
                if ('-' === $current[0] && false !== \strpos(" \t", $current[1])) {
                    $blocks[$block] .= "\n- " . \substr($current, 2);
                    continue;
                }
                if ($dent > 0) {
                    if ("\n- " === \substr($prev, -3)) {
                        $blocks[$block] .= $current;
                        continue;
                    }
                    $blocks[$block] .= "\n" . $current;
                    continue;
                }
            }
            $blocks[++$block] = $current;
        }
        $out = [];
        foreach ($blocks as $block) {
            if (false !== \strpos('"\'', $block[0]) && \preg_match('/^(' . str . '):\s+/', $block, $m)) {
                $out[v($m[1])] = v(\substr($block, \strlen($m[0])), $array, $lot);
                continue;
            }
            [$k, $s, $v] = \array_replace(["", "", ""], \preg_split('/[ \t]*:([ \n\t]\s*|$)/', $block, 2, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY));
            if ("" === $v) {
                $out[$k] = null;
                continue;
            }
            // Fix case for invalid key-value pair(s) such as `asdf: asdf: asdf` as it should be `asdf:\n asdf: asdf`
            if ("\n" !== \substr($s, -1) && false === \strpos('!&*:>[{|', $v[0]) && (false !== \strpos($v, ":\n") || false !== \strpos($v, ":\t") || false !== \strpos($v, ': '))) {
                $out[$k] = $v;
                continue;
            }
            $out[$k] = v($v, $array, $lot);
        }
        return $array ? $out : (object) $out;
    }
}