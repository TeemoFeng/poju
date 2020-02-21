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
    //峰会
    'wechat_login' =>[
    'appid' => 'wx5609266423c0e163',
    'secret'=> '67487b26e0bd56115db33307f1b7455f',
    ],

    //官网
//    'wechat_login' =>[
//        'appid' => 'wx844fa51cfccdf437',
//        'secret'=> 'e00137f4867283f3376c446144420399'
//    ],

];
