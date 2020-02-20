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
    ],
    'wechat_login' =>[
    'appid' => 'wx5609266423c0e163',
    'secret'=> '95ef1528aa0f72d59ba743ee6b136c0f',
],

];
