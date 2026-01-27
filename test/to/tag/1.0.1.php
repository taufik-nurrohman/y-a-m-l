<?php

return array(
  'asdf-1' => $s = file_get_contents(__DIR__ . '/../../asdf.png'),
  'asdf-2' => array('asdf' => $s),
  'asdf-3' => array('asdf' => array('asdf' => $s)),
  'asdf-4' => array('asdf' => array('asdf' => array('asdf' => $s))),
  'asdf-5' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => $s)))),
  'asdf-6' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => $s))))),
  'asdf-7' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => $s)))))),
  'asdf-8' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => array('asdf' => $s))))))),
  'asdf-9' => array('asdf' => $s)
);