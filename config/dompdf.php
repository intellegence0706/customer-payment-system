<?php

return [

        'show_warnings'       => false,
        'orientation'         => 'portrait',
        'default_paper_size'  => 'a4',
        'default_font'        => env('DOMPDF_DEFAULT_FONT', 'NotoSansJP'),
        'dpi'                 => 96,
        'enable_font_subsetting' => true,
        'dompdf_enable_unicode' => true,
    
        'defines' => [
            'font_dir'            => resource_path('fonts/'),
            'font_cache'          => storage_path('fonts/'),
            'temp_dir'            => storage_path('app/dompdf_temp'),
            'chroot'              => base_path(),
    
            'dpi'                 => 96,
            'default_font'        => env('DOMPDF_DEFAULT_FONT', 'NotoSansJP'),
            'isHtml5ParserEnabled'=> true,
            'enable_remote'       => true,   
            'enable_php'          => false, 
        ],
    
        'fonts' => [
            'NotoSansJP' => [
                'normal'      => resource_path('fonts/NotoSansJP.ttf')
            ],

        ],
    
];


