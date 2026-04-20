<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DaData: стандартизация ФИО (cleaner.dadata.ru)
    |--------------------------------------------------------------------------
    |
    | Ключи из личного кабинета https://dadata.ru/profile/#info
    | По умолчанию падеж — name_case. Для тела документа можно задать явно: {{field:id|genitive}}, {{user.name|dative}}.
    |
    */

    'enabled' => filter_var(env('DADATA_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'token' => env('DADATA_TOKEN', ''),

    'secret' => env('DADATA_SECRET', ''),

    /**
     * Падеж для подстановки в шапку и подвал PDF:
     * nominative | genitive | dative | ablative | instrumental
     */
    'name_case' => env('DADATA_NAME_CASE', 'dative'),

    'timeout' => (int) env('DADATA_TIMEOUT', 5),

    'cache_ttl' => (int) env('DADATA_CACHE_TTL', 86400),

];
