<?php

return array(
    'dsn' => env('SENTRY_LARAVEL_DSN', 'https://8ae3e6c99d44426cb5686f95e3c5fdbe@sentry.io/1449920'),
    // capture release as git sha
    // 'release' => trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,
);
