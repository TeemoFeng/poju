<?php
return [
    //分页配置
    'paginate'               => [
        'type'      => 'MyPage',
        'var_page'  => 'page',
        'list_rows' => 20,
    ],
    'view_replace_str'  =>  [
        '__IMG__'=>'/static/images',
        '__F__' => '/static/fonts'
    ]
];
