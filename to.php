<?php

namespace x\y_a_m_l {
    function to($value, $state = []): ?string {
        if (!\is_array($state)) {
            $state = ['tab' => !!$state];
        }
        $state = \array_replace([
            'batch' => false,
            'tab' => 4,
            'with' => []
        ], $state);
        $batch = !empty($state['batch']);
        $tab = $state['tab'] ?? 4;
        if (\is_int($tab)) {
            $tab = \str_repeat(' ', $tab > 0 ? $tab : 4);
        } else if (true === $tab || !\is_string($tab)) {
            $tab = \str_repeat(' ', 4);
        }
        $with = (array) ($state['with'] ?? []);
        if ($with) foreach ($with as $w) {
            if (\is_array($w) && \is_callable($w[0] ?? 0)) {
                $value = $w[0]($value, $state);
            }
        }
        if ($batch) {
            $t = '!"&*>[{|' . "'";
            if (!\is_array($value) || !to\l($value)) {
                $value = to\v($value, $tab);
                return '---' . ("" !== $value && \strspn($value, $t) ? ' ' : "\n") . $value;
            }
            $r = "";
            foreach ($value as $v) {
                $v = to\v($v, $tab);
                $r .= "\n---" . ("" !== $v && \strspn($v, $t) ? ' ' : "\n") . $v;
            }
            $r = [\substr($r, 1), $state, \count($value)];
        } else {
            $r = [to\v($value, $tab), $state, null];
        }
        if ($with) foreach ($with as $w) {
            if (\is_array($w) && \is_callable($w[1] ?? 0)) {
                $r[0] = $w[1](...$r);
            } else if (\is_callable($w)) {
                $r[0] = $w(...$r);
            }
        }
        return $r[0];
    }
}

namespace x\y_a_m_l\to {
    function l(array $v) {
        if ([] === $v) {
            return true;
        }
        // PHP >=8.1
        if (\function_exists("\\array_is_list")) {
            return \array_is_list($v);
        }
        $k = -1;
        foreach ($v as $kk => $vv) {
            if ($kk !== ++$k) {
                return false;
            }
        }
        return true;
    }
    function q(string $v): string {
        if ("" === $v) {
            return '""';
        }
        if (\is_numeric($v)) {
            return "'" . $v . "'";
        }
        if (
            // ` asdf` or `asdf `
            ' ' === $v[0] || ' ' === \substr($v, -1) ||
            // `asdf:`
            ':' === \substr($v, -1) ||
            // `asdf #asdf`
            false !== ($n = \strpos($v, '#')) && \strspn($v, " \n\t", $n - 1) ||
            // `asdf: asdf`
            false !== ($n = \strpos($v, ':')) && \strspn($v, " \n\t", $n + 1) ||
            // <https://yaml.org/spec/1.2.2#56-miscellaneous-characters>
            // <https://yaml.org/spec/1.2.2#example-invalid-use-of-reserved-indicators>
            \strspn($v, '!"#%&*+,-.:>?@[]`{|}' . "'\\") ||
            \strcspn($v, "<=>[\\]{|}") !== \strlen($v) ||
            false !== \strpos(',false,null,true,~,', ',' . \strtolower($v) . ',')
        ) {
            return "'" . \strtr($v, [
                "'" => "''"
            ]) . "'";
        }
        if (\strspn($v, "\n\t") || \strspn($v, "\n\t", -1) || $v !== \addcslashes($v, "\\")) {
            return \json_encode($v);
        }
        return $v;
    }
    function r(string $v, string $c = ""): string {
        $r = [];
        foreach (\explode("\n", $v) as $vv) {
            $r[] = "" === \trim($vv) ? \rtrim($c) : $c . $vv;
        }
        return \implode("\n", $r);
    }
    function v($value, string $tab, int $level = 1) {
        if (false === $value) {
            return 'false';
        }
        if (null === $value) {
            return '~';
        }
        if (true === $value) {
            return 'true';
        }
        if (\is_float($value)) {
            if (\is_infinite($value)) {
                return '.INF';
            }
            if (\is_nan($value)) {
                return '.NAN';
            }
            $value = (string) $value;
            return false === \strpos($value, '.') ? $value . '.0' : $value;
        }
        if (\is_int($value)) {
            return (string) $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }
        if (\is_string($raw = $value)) {
            $max = \max(60, 120 - (\strlen($tab) * $level + 1));
            if ("" !== $value && false !== \strpos($value, "\0")) {
                $value = \base64_encode($value);
                if (\strlen($value) <= $max) {
                    return '!!binary ' . $value;
                }
                return "!!binary |\n" . $tab . \rtrim(\chunk_split($value, $max, "\n" . $tab));
            }
            $d = 0;
            $flow = false;
            $style = false === \strpos(\trim($value), "\n") ? '>' : '|';
            foreach (\explode("\n", $value) as $v) {
                if ("" === $v) {
                    continue;
                }
                if (0 === ($test = \strspn($v, ' '))) {
                    break;
                }
                $d = $test > $d && $d > 0 ? $d : $test;
            }
            if ($d > 0) {
                $value = \substr(\strtr($value, [
                    "\n" . \str_repeat(' ', $d) => "\n"
                ]), $d);
                $d = (string) $d;
            } else {
                $d = "";
            }
            if ('>' === $style && \strlen($value) > $max) {
                $flow = true;
                $value = \wordwrap($value, $max, "\n");
            }
            $v = "" !== $d ? \str_repeat(' ', (int) $d) : "";
            $value = $v . r(\strtr($value, [
                "\n" => "\n" . $tab . $v
            ]));
            if ("\n" === \substr($value, -1)) {
                if (\strspn($value, " \n\t", -2)) {
                    return $style . '+' . $d . "\n" . $tab . $value;
                }
                return $style . $d . "\n" . $tab . $value;
            }
            if ($flow || '|' === $style) {
                return $style . '-' . $d . "\n" . $tab . $value;
            }
            return q($raw);
        }
        if (\is_array($value) && l($value)) {
            if ([] === $value) {
                return '[]';
            }
            $r = [];
            $short = 0;
            foreach ($value as $v) {
                if (\is_string($v) && ("" === $v || \strlen($v) < 41)) {
                    $short += 1;
                } else if (\is_float($v) || \is_int($v) || \in_array($v, [-\INF, -\NAN, \INF, \NAN, false, null, true], true)) {
                    $short += 1;
                } else {
                    $short = 6; // Disable flow style value!
                }
                $v = v($v, $tab, $level);
                if (\strspn($v, '>|')) {
                    $short = 6; // Disable flow style value!
                }
                $r[] = r(\strtr($v, [
                    "\n" => "\n  "
                ]));
            }
            // Prefer flow style value?
            if ($short < 6) {
                return '[ ' . \implode(', ', $r) . ' ]';
            }
            return '- ' . \implode("\n- ", $r);
        }
        if (\is_object($value)) {
            if ($value instanceof \stdClass && [] === (array) $value) {
                return '{}';
            }
            if (\method_exists($value, '__toString')) {
                return "|-\n" . $tab . r(\trim(\strtr($value->__toString(), [
                    "\n" => "\n" . $tab
                ]), "\n"));
            }
        }
        if (\is_iterable($value)) {
            $r = [];
            $short = 0;
            foreach ($value as $k => $v) {
                $k = "\0" === $k ? "? ~\n" : (\is_string($k) && false !== \strpos($k, "\n") ? '? ' . v($k, '  ', $level) . "\n" : q((string) $k));
                if (\is_string($v) && ("" === $v || \strlen($v) < 41)) {
                    $short += 1;
                } else if (\is_float($v) || \is_int($v) || \in_array($v, [-\INF, -\NAN, \INF, \NAN, false, null, true], true)) {
                    $short += 1;
                } else {
                    $short = 4; // Disable flow style value!
                }
                if (\is_iterable($v)) {
                    $v = v($v, $tab, $level + 1);
                    if (\strspn($v, '[{')) {
                        $v = ' ' . $v;
                    } else {
                        $v = r("\n" . $tab . \strtr($v, [
                            "\n" => "\n" . $tab
                        ]));
                    }
                    $r[] = $k . ':' . $v;
                    continue;
                }
                if ('~' === ($v = v($v, $tab, $level)) && '?' === ($k[0] ?? 0)) {
                    $r[] = \substr($k, 0, -1);
                    continue;
                }
                $r[] = $k . ': ' . $v;
            }
            // Prefer flow style value?
            if ($short < 4 && '?' !== ($k[0] ?? 0)) {
                return '{ ' . \implode(', ', $r) . ' }';
            }
            return "" !== ($value = \implode("\n", $r)) ? $value : null;
        }
        try {
            return q((string) $value);
        } catch (\Throwable $e) {
            return "~\n" . r((string) $e, '#! ');
        }
    }
}