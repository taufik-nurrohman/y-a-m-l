<?php

return array(
  array(
    "", 'asdf'
  ),
  array(
    "''", "'asdf'"
  ),
  array(
    '""', '"asdf"'
  ),
  array(
    ' asdf asdf',
    'asdf asdf ',
    'asdf asdf'
  ),
  array(
    "\nasdf asdf", "\nasdf\nasdf",
    "asdf asdf\n", "asdf\nasdf\n",
    "asdf\nasdf", "asdf\n\nasdf"
  ),
  array(
    '!asdf', 'asdf!asdf', 'asdf!',
    '#asdf', 'asdf#asdf', 'asdf#', '# asdf', 'asdf # asdf', 'asdf #',
    '&asdf', 'asdf&asdf', 'asdf&', '& asdf', 'asdf & asdf', 'asdf &',
    '*asdf', 'asdf*asdf', 'asdf*', '* asdf', 'asdf * asdf', 'asdf *',
    '+asdf', 'asdf+asdf', 'asdf+', '+ asdf', 'asdf + asdf', 'asdf +',
    ',asdf', 'asdf,asdf', 'asdf,', ', asdf', 'asdf , asdf', 'asdf ,',
    '-asdf', 'asdf-asdf', 'asdf-', '- asdf', 'asdf - asdf', 'asdf -',
    '.asdf', 'asdf.asdf', 'asdf.', '. asdf', 'asdf . asdf', 'asdf .',
    '1asdf', 'asdf1asdf', 'asdf1', '1 asdf', 'asdf 1 asdf', 'asdf 1',
    ':asdf', 'asdf:asdf', 'asdf:', ': asdf', 'asdf : asdf', 'asdf :',
    '@asdf', 'asdf@asdf', 'asdf@',
    '[asdf', 'asdf[asdf', 'asdf[',
    ']asdf', 'asdf]asdf', 'asdf]',
    '{asdf', 'asdf{asdf', 'asdf{',
    '}asdf', 'asdf}asdf', 'asdf}',
  ),
  array(
    '.1', '+.1', '-.1',
    '0.1', '+0.1', '-0.1',
    '1', '+1', '-1',
  ),
  array(
    'asdf: asdf'
  )
);