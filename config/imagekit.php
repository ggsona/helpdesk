<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ImageKit.io Configuration
    |--------------------------------------------------------------------------
    |
    | Credenciales para conectar con ImageKit.io como servicio de
    | almacenamiento de archivos externo. Obtén estas claves desde:
    | https://imagekit.io/dashboard/developer/api-keys
    |
    */

    'public_key'   => env('IMAGEKIT_PUBLIC_KEY', ''),
    'private_key'  => env('IMAGEKIT_PRIVATE_KEY', ''),
    'url_endpoint' => env('IMAGEKIT_URL_ENDPOINT', ''),

];
