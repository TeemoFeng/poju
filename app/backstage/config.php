<?php
return [
    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 20,
    ],
    //上传配置
    'file_upload' =>[
        'image' => ['jpg','png','gif','bmp','jpeg','svg'],
        'media' => ['flv','swf','mkv','avi','rm','rmvb','mpeg','mpg','ogg','ogv','mov','wmv','mp4','webm','mp3','wav','mid'],
        'zip' => ['rar','zip','tar','gz','7z','bz2','cab','iso'],
        'doc' => ['doc','docx','xls','xlsx','ppt','pptx','pdf','txt','md','xml'],
    ]
];
