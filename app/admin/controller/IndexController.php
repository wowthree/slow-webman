<?php

namespace app\admin\controller;

use support\Request;
use app\admin\Admin;
use app\admin\SlowAdmin;
use support\Response;
use app\model\Extension;
use JsonSerializable;

class IndexController extends AdminController
{
    public function menus(): Response|JsonSerializable
    {
        $menus = [
            [
                'name'      => 'user_setting',
                'path'      => '/user_setting',
                'component' => 'amis',
                'meta'      => [
                    'hide'         => true,
                    'title'        => __('admin.user_setting'),
                    'icon'         => 'carbon:user-avatar',
                    'singleLayout' => 'basic',
                ],
            ],
        ];

        array_push($menus, ...SlowAdmin::make()->getMenus());

        if (config('admin.show_development_tools')) {
            $menus[] = $this->devTools();
        }

        return $this->response()->success($menus);
    }

    public function noContent(): Response|JsonSerializable
    {
        return $this->response()->successMessage();
    }

    public function devTools(): array
    {
        return [
            'name'      => 'dev_tools',
            'path'      => '/dev_tools',
            'component' => 'basic',
            'meta'      => [
                'title' => __('admin.developer'),
                'icon'  => 'fluent:window-dev-tools-20-regular',
            ],
            'children'  => [
                [
                    'name'      => 'dev_tools_code_generator',
                    'path'      => '/dev_tools/code_generator',
                    'component' => 'amis',
                    'meta'      => [
                        'title' => __('admin.code_generator'),
                        'icon'  => 'material-symbols:code-rounded',
                    ],
                ],
            ],
        ];
    }

    public function settings(): Response|JsonSerializable
    {
        $settings = [
            'app_name'               => config('admin.name'),
            'logo'                   => url(config('admin.logo')),
            'locale'                 => config('app.locale'),
            'enabled_extensions'     => Extension::query()->where('is_enabled', 1)->pluck('name')?->toArray(),
            'login_captcha'          => config('admin.auth.login_captcha'),
            'system_theme_setting'   => Admin::setting()->get('system_theme_setting'),
            'show_development_tools' => config('admin.show_development_tools'),
            'assets'                 => Admin::getAssets(),
        ];

        return $this->response()->success($settings);
    }

    /**
     * 保存设置项
     *
     * @param Request $request
     *
     * @return Response|JsonSerializable
     */
    public function saveSettings(Request $request)
    {
        Admin::setting()->setMany($request->all());

        return $this->response()->successMessage();
    }
}
