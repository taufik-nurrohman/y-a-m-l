<?php

// Not string-able, not traverse-able
class Test_4_1_1 {
    public function __construct() {}
}

return array(
  'asdf' => new Test_4_1_1
);