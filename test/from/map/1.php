<?php

return (object) array(
  'asdf-1' => 1,
  'asdf-2 asdf-2' => array(
    'asdf',
    'asdf',
    'asdf'
  ),
  '' . "\0" . 'O:8:"stdClass":2:{s:6:"asdf-3";i:3;s:6:"asdf-4";i:4;}' . "\0" . '' => (object) array(
    'asdf-5' => 5,
    'asdf-6' => 6
  ),
  '' . "\0" . 'O:8:"stdClass":1:{s:6:"' . "\0" . 'b:0;' . "\0" . '";b:0;}' . "\0" . '' => false,
  '' . "\0" . 'O:8:"stdClass":1:{s:4:"' . "\0" . 'N;' . "\0" . '";N;}' . "\0" . '' => null,
  '' . "\0" . 'O:8:"stdClass":1:{s:6:"' . "\0" . 'b:1;' . "\0" . '";b:1;}' . "\0" . '' => true
);