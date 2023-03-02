<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;
use app\admin\controller;
use app\middleware\AuthCheck;

Route::get('/admin', function () {
    return response()->file(base_path('public/admin/index.html'));
});

Route::group('/' . trim(config('admin.route.prefix'), '/'), function () {
    Route::post('/login', [controller\AuthController::class, 'login']);
    Route::get('/logout', [controller\AuthController::class, 'logout']);
    Route::get('/current-user', [controller\AuthController::class, 'currentUser']);
    Route::get('/captcha', [controller\AuthController::class, 'reloadCaptcha']);

    Route::get('/no-content', [controller\IndexController::class, 'noContent']);
    Route::get('/menus', [controller\IndexController::class, 'menus']);
    Route::get('/_settings', [controller\IndexController::class, 'settings']);
    Route::post('/_settings', [controller\IndexController::class, 'saveSettings']);

    // 用户设置
    Route::get('/user_setting', [controller\AuthController::class, 'userSetting']);
    Route::put('/user_setting/{id}', [controller\AuthController::class, 'saveUserSetting']);
    // 图片上传
    Route::any('/upload_image', [controller\IndexController::class, 'uploadImage']);
    // 文件上传
    Route::any('/upload_file', [controller\IndexController::class, 'uploadFile']);
    // 富文本编辑器上传
    Route::any('/upload_rich', [controller\IndexController::class, 'uploadRich']);

    // 主页
    Route::resource('dashboard', controller\HomeController::class);

    Route::group('/system', function () {
        Route::get('/', [controller\AdminUserController::class, 'index']);
        // 管理员
        Route::resource('/admin_users', controller\AdminUserController::class);
        // 菜单
        Route::resource('/admin_menus', controller\AdminMenuController::class);
        // 快速编辑
        Route::post('/admin_menu_quick_save', [controller\AdminMenuController::class, 'quickEdit']);
        // 角色
        Route::resource('/admin_roles', controller\AdminRoleController::class);
        // 权限
        Route::resource('/admin_permissions', controller\AdminPermissionController::class);
        // 自动生成权限
        Route::post('/_admin_permissions_auto_generate', [controller\AdminPermissionController::class, 'autoGenerate']);
        // 系统设置
        Route::resource('/settings', controller\SettingController::class);
    });


    // 开发工具
    Route::group('/dev_tools', function () {
        Route::get('/', [controller\DevTools\CodeGeneratorController::class, 'index']);
        // 代码生成器
        Route::resource('code_generator', controller\DevTools\CodeGeneratorController::class);
    });
})->middleware([AuthCheck::class]);
