<?php

namespace app\admin\controller;

use support\Request;
use app\admin\renders\Tab;
use app\admin\renders\Tabs;
use app\admin\renders\Alert;
use app\admin\renders\InputKV;
use app\admin\renders\TextControl;


class SettingController extends AdminController
{
    protected string $queryPath = 'system/settings';

    protected string $pageTitle = '系统设置';

    public function index()
    {
        $page = $this->basePage()->body([
            Alert::make()->showIcon(true)->body("此处设置项无实际意义。"),
            $this->form(),
        ]);

        return $this->response()->success($page);
    }

    public function form()
    {
        return $this->baseForm()
            ->redirect('')
            ->api($this->getStorePath())
            ->data(settings()->all())
            ->body(
                Tabs::make()->tabs([
                    Tab::make()->title('基本设置')->body([
                        TextControl::make()->label('网站名称')->name('site_name'),
                        InputKV::make()->label('附加配置')->name('addition_config'),
                    ]),
                    Tab::make()->title('上传设置')->body([
                        TextControl::make()->label('上传域名')->name('upload_domain'),
                        TextControl::make()->label('上传路径')->name('upload_path'),
                    ]),
                ])
            );
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'site_name',
            'addition_config',
            'upload_domain',
            'upload_path',
        ]);

        return settings()->adminSetMany($data);
    }
}
