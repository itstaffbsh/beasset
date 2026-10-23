<?php

use Maatwebsite\Excel\Excel;

return [
    'exports' => [
        'chunk_size'             => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv'                    => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'line_ending'            => PHP_EOL,
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
            'output_encoding'        => '',
            'test_auto_detect'       => true,
        ],
        'properties'             => [
            'creator'        => '',
            'lastModifiedBy' => '',
            'title'          => '',
            'description'    => '',
            'subject'        => '',
            'keywords'       => '',
            'category'       => '',
            'manager'        => '',
            'company'        => '',
        ],
    ],

    'imports'            => [
        'read_only'    => true,
        'ignore_empty' => false,
        'heading_row'  => [
            'formatter' => 'slug',
        ],
        'csv'          => [
            'delimiter'        => null,
            'enclosure'        => '"',
            'escape_character' => '\\',
            'contiguous'       => false,
            'input_encoding'   => 'UTF-8',
        ],
        'properties'   => [
            'creator'        => '',
            'lastModifiedBy' => '',
            'title'          => '',
            'description'    => '',
            'subject'        => '',
            'keywords'       => '',
            'category'       => '',
            'manager'        => '',
            'company'        => '',
        ],
    ],

    'extension_detector' => [
        'xlsx'     => Excel::XLSX,
        'xlsm'     => Excel::XLSX,
        'xltx'     => Excel::XLSX,
        'xltm'     => Excel::XLSX,
        'xls'      => Excel::XLS,
        'xlt'      => Excel::XLS,
        'ods'      => Excel::ODS,
        'ots'      => Excel::ODS,
        'slk'      => Excel::SLK,
        'xml'      => Excel::XML,
        'gnumeric' => Excel::GNUMERIC,
        'html'     => Excel::HTML,
        'htm'      => Excel::HTML,
        'csv'      => Excel::CSV,
        'tsv'      => Excel::TSV,
        'pdf'      => Excel::DOMPDF,
    ],

    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        'driver'     => 'memory',
        'batch'      => [
            'memory_limit' => 60000,
        ],
        'illuminate' => [
            'store' => null,
        ],
        'default_ttl' => 10800,
    ],

    'transactions' => [
        'handler' => 'db',
        'db'      => [
            'connection' => null,
        ],
    ],

    'temporary_files' => [
        /*
        |--------------------------------------------------------------------------
        | Local Temporary Path
        |--------------------------------------------------------------------------
        | Cek apakah direktori storage/framework/cache/laravel-excel dapat ditulis.
        | Jika tidak (misal permission denied di Linux VPS), fallback otomatis ke
        | direktori temporary sistem (sys_get_temp_dir() / /tmp) yang selalu writable.
        */
        'local_path'          => (function () {
            $defaultPath = storage_path('framework/cache/laravel-excel');
            if (is_dir($defaultPath) && is_writable($defaultPath)) {
                return $defaultPath;
            }
            if (!file_exists($defaultPath) && @mkdir($defaultPath, 0777, true)) {
                return $defaultPath;
            }
            return sys_get_temp_dir();
        })(),

        'local_permissions'   => [
            'dir'  => 0777,
            'file' => 0666,
        ],

        'remote_disk'         => null,
        'remote_prefix'       => null,
        'force_resync_remote' => null,
    ],
];
