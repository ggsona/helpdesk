<?php

return [
    'enabled' => env('AD_ENABLED', false),
    'host' => env('LDAP_HOST', 'ldap://127.0.0.1'),
    'port' => env('LDAP_PORT', 389),
    'encryption' => env('LDAP_ENCRYPTION', 'none'),
    'base_dn' => env('LDAP_BASE_DN', ''),
    'user' => env('LDAP_USERNAME', ''),
    'password' => env('LDAP_PASSWORD', ''),
    'attribute' => env('LDAP_USER_ATTRIBUTE', 'samaccountname'),
    'attr_name' => env('LDAP_ATTR_NAME', 'displayName'),
    'attr_email' => env('LDAP_ATTR_EMAIL', 'mail'),
    'provider' => env('LDAP_PROVIDER', 'activedirectory'),
];
