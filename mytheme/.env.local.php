<?php

// LOCAL DEV — do not commit this version. To restore prod: composer dump-env prod

return array(
    'APP_ENV' => 'dev',
    'APP_DEBUG' => true,
    'HIDE_DISCOUNT' => false,
    'APP_SECRET' => 'dev-local-secret-change-for-prod',
    'ROOT_URI' => function_exists('get_theme_root_uri') ? get_theme_root_uri() . '/' . basename(__DIR__) : '',
);
