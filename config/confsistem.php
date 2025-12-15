<?php

return [
    'expired_time' => env('TOKEN_EXPIRED_TIME', 60), // default 60 menit
    'estimasi_layan' => env('ESTIMASI_LAYAN', 5), // default 5 menit
    'addapi_url' => env('ADDAPI_URL', 'http://localhost/webservice-api'), // default URL API tambahan
    'add_farmasi' => env('ADD_FARMASI', 'TIDAK'),
    'url_antrol' => env('URL_ANTROL', '')
];
