<?php

return [
    's3' => [
        'aws_url' => env('AWS_URL', 'https://sgeps-bucket.s3-eu-west-1.amazonaws.com'),
        'main_storage_path' => env('APP_ENV') == 'production' ? 'prod/' : 'dev/',
        'stogare_path_clientes' => env('APP_ENV') == 'production' ? 'prod/clientes/' : 'dev/clientes/',
        'storage_path_empresa' => 'empresas/',
        'storage_path_farmacia' => 'farmacias/',
        'storage_path_u_sanitaria' => 'unidades-sanitarias/',

    ],
];
