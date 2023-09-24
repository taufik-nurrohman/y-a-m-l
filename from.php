<?php

namespace x\y_a_m_l {
    function from(?string $value, $array = false, array &$lot = []) {
        if ("" === ($value = \trim($raw = $value ?? ""))) {
            return null;
        }
        // Normalize line break(s)
        $value = \strtr($value, [
            "\r\n" => "\n",
            "\r" => "\n"
        ]);
        $str = '"(?>[^"\\\\]|\\\\.)*"|\'(?>\'\'|[^\'])*\'';
        if (false !== \strpos('\'"', $value[0]) && \preg_match('/(' . $str . ')\s*#.*$/', $value, $m)) {
            return from($m[1]);
        }
        if ("'" === $value[0] && "'" === \substr($value, -1)) {
            return from\f(\strtr(\substr($value, 1, -1), [
                "''" => "'"
            ]), false);
        }
        if ('"' === $value[0] && '"' === \substr($value, -1)) {
            try {
                $value = \json_decode($value, false, 1, \JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                $value = \strtr(\substr($value, 1, -1), [
                    '\"' => '"'
                ]);
            }
            return from\f($value, false);
        }
        // Fold-style or literal-style value
        if (false !== \strpos('>|', $value[0])) {
            [$rule, $content] = \explode("\n", $raw, 2);
            // Remove comment(s)
            $content = \preg_replace('/^#.*$/m', "", $content);
            $rule = \trim(\strstr($rule, '#', true) ?: $rule);
            // Get indent size to remove
            $dent = \strspn(\trim($content, "\n"), ' ');
            // Remove indent(s)
            $content = \substr(\strtr("\n" . $content, [
                "\n" . \str_repeat(' ', $dent) => "\n"
            ]), 1);
            if (isset($rule[1])) {
                $cut = \substr($rule, -1);
                // `>4`
                if (\is_numeric($cut)) {
                    $cut = "";
                    $dent = (int) \substr($rule, 1);
                // `>4+`
                } else {
                    $dent = (int) \substr($rule, 1, -1);
                }
            // `>`
            } else {
                $cut = "";
                $dent = 0;
            }
            if ("" !== $cut && false === \strpos('+-', $cut)) {
                return null; // :(
            }
            if ('+' !== $cut) {
                $content = \rtrim($content) . ("" === $cut ? "\n" : "");
            }
            if ('>' === $rule[0]) {
                $content = from\f($content);
            }
            if ($dent > 0) {
                $d = \str_repeat(' ', $dent);
                $content = \substr(\strtr("\n" . $d . \strtr($content, [
                    "\n" => "\n" . $d
                ]), [
                    "\n" . $d . "\n" => "\n\n"
                ]), 1);
            }
            return $content;
        }
        // Remove comment(s)
        if ('[' === $value[0] && ']' === \substr($value, -1) || '{' === $value[0] && '}' === \substr($value, -1)) {
            $out = "";
            // Validate to JSON
            $value = \preg_replace('/#[^\n]+(?=\n|$)/', "", $value); // Remove comment(s)
            foreach (\preg_split('/\s*(' . $str . '|[\[\]\{\}:,])\s*/', $value, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) as $v) {
                if ('~' === $v) {
                    $out .= 'null';
                    continue;
                }
                if ('FALSE' === $v || 'NULL' === $v || 'TRUE' === $v || 'False' === $v || 'Null' === $v || 'True' === $v || 'false' === $v || 'null' === $v || 'true' === $v) {
                    $out .= ':' === \substr($out, -1) ? \strtolower($v) : '"' . $v . '"';
                    continue;
                }
                if (\is_numeric($v)) {
                    $out .= false !== \strpos(',:[', \substr($out, -1)) ? $v : '"' . $v . '"';
                    continue;
                }
                $out .= false !== \strpos(',:[]{}', $v) ? $v : \json_encode(from($v, $array, $lot), false, 1);
            }
            // `{1:a,2:b,3:c}`
            $out = \preg_replace('/([+-]?\d*[.]?\d+):/', '"$1":', $out);
            return \json_decode(\strtr(\strtr($out, [
                // `[1,2,3,]`
                ',]' => ']',
                // `{a:1,b:2,c:3,}`
                ',}' => '}',
            ]), [
                // `{a:,b:0}`
                ':,' => ':null,',
                // `{a:0,b:}`
                ':}' => ':null}'
            ])) ?? $value;
        }
        $value = \trim(\strstr($value, '#', true) ?: $value);
        if ('.INF' === $value || '.Inf' === $value || '.inf' === $value || '+.INF' === $value || '+.Inf' === $value || '+.inf' === $value) {
            return \INF;
        }
        if ('-.INF' === $value || '-.Inf' === $value || '-.inf' === $value) {
            return -\INF;
        }
        if ('.NAN' === $value || '.Nan' === $value || '.nan' === $value || '+.NAN' === $value || '+.Nan' === $value || '+.nan' === $value) {
            return \NAN;
        }
        if ('-.NAN' === $value || '-.Nan' === $value || '-.nan' === $value) {
            return -\NAN;
        }
        if ('""' === $value || "''" === $value) {
            return "";
        }
        if ('[]' === $value) {
            return [];
        }
        if ('FALSE' === $value || 'False' === $value || 'false' === $value) {
            return false;
        }
        if ('NULL' === $value || 'Null' === $value || 'null' === $value || '~' === $value) {
            return null;
        }
        if ('TRUE' === $value || 'True' === $value || 'true' === $value) {
            return true;
        }
        if ('{}' === $value) {
            return $array ? [] : (object) [];
        }
        // A tag
        if ('!' === $value[0]) {
            // TODO
        }
        // A comment
        if ('#' === $value[0]) {
            return null;
        }
        // <https://yaml.org/spec/1.2.2#692-node-anchors>
        if (false !== \strpos('&*', $value[0]) && \preg_match('/^([&*])([^\s,\[\]{}]+)(\s+|$)/', $value, $m)) {
            if ('&' === $m[1]) {
                return ($lot[0][$m[2]] = from($value = \substr($value, \strlen($m[0])), $array, $lot));
            }
            return $lot[0][$m[2]] ?? null;
        }
        // List-style value
        if ('-' === $value[0] && \strlen($value) > 2 && false !== \strpos(" \n\t", $value[1])) {
            $out = [];
            foreach (\preg_split('/\n-[ \n\t]/', \substr($value, 2)) as $v) {
                $out[] = from($v, $array, $lot);
            }
            return $out;
        }
        if (\strlen($value) > 2 && '0' === $value[0]) {
            // `0xC`
            if (\preg_match('/^0x[a-f\d]+$/i', $value)) {
                return \hexdec($value);
            }
            // `0777` or `0O777` or `0o777`
            if (\preg_match('/^0o?[0-7]+$/i', $value)) {
                if (false !== \strpos('Oo', $value[1])) {
                    // PHP < 8.1
                    $value = \substr($value, 2);
                }
                return \octdec($value);
            }
        }
        if (\preg_match('/^[+-]?\d*[.]?\d+e[+-]?\d+$/i', $value)) {
            return (float) $value;
        }
        if (\is_numeric($value)) {
            return false !== \strpos($value, '.') ? (float) $value : (int) $value;
        }
        if (\is_numeric($value[0]) && \preg_match('/^[1-9]\d{3,}-(0\d|1[0-2])-(0\d|[1-2]\d|3[0-1])([ t]([0-1]\d|2[0-4])(:([0-5]\d|60)){2}([.]\d+)?([+-]([0-1]\d|2[0-4])(:([0-5]\d|60)){2}([.]\d+)?)?z?)?$/i', $value)) {
            return new \DateTime($value);
        }
        if (false === ($n = \strpos($value, ':')) || false === \strpos(" \t", \substr($value, $n + 1, 1))) {
            return from\f($value, false);
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
                // A comment
                if ('#' === $current[0]) {
                    continue;
                }
                // A list
                if ('-' === \trim(\strstr($current, '#', true) ?: $current)) {
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
            if (false !== \strpos('\'"', $block[0]) && \preg_match('/^(' . $str . '):\s+/', $block, $m)) {
                $out[from($m[1])] = from(\substr($block, \strlen($m[0])), $array, $lot);
                continue;
            }
            [$k, $s, $v] = \array_replace(["", "", ""], \preg_split('/[ \t]*:([ \n\t]\s*|$)/', $block, 2, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY));
            if ("" === $v) {
                $out[$k] = null;
                continue;
            }
            // Fix case for invalid key-value pair(s) such as `asdf: asdf: asdf` as it should be `asdf:\n asdf: asdf`
            if ($s && "\n" !== $s[0] && false === \strpos("\n!#&*:>[{|", $v[0])) {
                $out[$k] = $v;
                continue;
            }
            $out[$k] = from($v, $array, $lot);
        }
        return $array ? $out : (object) $out;
    }
}

namespace x\y_a_m_l\from {
    // <https://yaml-multiline.info>
    function f(string $value, $dent = true) {
        $content = "";
        $test = 0;
        foreach (\explode("\n", $value) as &$v) {
            if ("" === $v) {
                $content .= "\n";
                continue;
            }
            if ($dent && $test !== ($t = \strspn($v, " \t"))) {
                $test = $t;
                $content .= "\n" . $v;
                continue;
            }
            $content .= ("\n" !== \substr($content, -1) ? ' ' : "") . \ltrim($v);
        }
        return \ltrim($content);
    }
}