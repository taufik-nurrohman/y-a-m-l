<?php

// Is string-able
class Test_4_1 {
    public $v;
    public function __construct(array $v) {
        $this->v = $v;
    }
    public function __toString(): string {
        return implode("\n", $this->v);
    }
}

return array(
  'asdf-1' => new Error('asdf'),
  'asdf-2' => new Test_4_1(['asdf', 'asdf', "", 'asdf'])
);