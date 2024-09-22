<?php

namespace RexFeed;

// Don't redefine the functions if included multiple times.
if (!\function_exists('RexFeed\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
