<?php

return [
    // 应用名称
    'name'           => 'Slow Webman',
    // 应用 logo
    'logo'           => '/admin/logo.png',
    // 默认头像
    'default_avatar' => '/admin/default-avatar.png',

    'route' => [
        'prefix'     => 'admin-api', // 路由默认前缀
        'namespace'  => 'app\\admin\\controller', // 用于开发者工具
    ],
    // 登陆验证
    'auth' => [
        'login_captcha' => false,
        'enable'        => true,
        'except'        => [], // 无需登陆的路由
    ],
    // 上传文件默认在public文件夹下
    'upload' => [
        'disk'      => 'upload', // public 文件夹下的子文件夹名称
        // Image and file upload path under the disk above.
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
            'rich'  => 'rich',
        ],
    ],

    'https' => false,
    // 开发者工具
    'show_development_tools'               => true,
    // 是否显示 [权限] 功能中的自动生成按钮
    'show_auto_generate_permission_button' => true,
    // 拓展
    'extension' => [
        'dir' => base_path('extensions'),
    ],
];
