<?php

return [
    // API menggunakan Bearer token; login web tetap menggunakan session.
    'guard' => [],
    'routes' => false,
    'expiration' => 10080,
];
