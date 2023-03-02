<?php

namespace app\admin\controller;

use support\Response;
use app\admin\renders\Page;
use app\admin\renders\Html;
use app\admin\renders\Form;
use app\model\AdminMenu;
use app\admin\renders\Dialog;
use app\admin\renders\SvgIcon;
use app\admin\renders\Operation;
use app\admin\renders\TableColumn;
use app\admin\renders\TextControl;
use app\admin\renders\ListControl;
use app\admin\renders\GroupControl;
use app\admin\renders\NumberControl;
use app\admin\renders\SwitchControl;
use app\service\AdminMenuService;
use JsonSerializable;
use app\admin\renders\TreeSelectControl;

class AdminMenuController extends AdminController
{
    protected string $serviceName = AdminMenuService::class;

    protected string $queryPath = 'system/admin_menus';

    protected string $pageTitle;

    public function __construct()
    {
        $this->pageTitle = __('admin.admin_menus');

        parent::__construct();
    }

    public function index(): Response|JsonSerializable
    {
        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->list());
        }

        return $this->response()->success($this->list());
    }

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->loadDataOnce(true)
            ->footerToolbar([])
            ->headerToolbar([
                $this->createButton(true)->dialog(
                    Dialog::make()->title(__('admin.create'))->body(
                        $this->form()->api($this->getStorePath())
                    )->size('lg')
                ),
                'bulkActions',
                amis('reload')->align('right'),
                amis('filter-toggler')->align('right'),
            ])
            ->filterTogglable(false)
            ->footerToolbar(['statistics'])
            ->quickSaveApi(admin_url('system/admin_menu_quick_save'))
            ->bulkActions([$this->bulkDeleteButton()->reload('window')])
            ->columns([
                TableColumn::make()->label('ID')->name('id')->sortable(true),
                TableColumn::make()->label(__('admin.admin_menu.title'))->name('title'),
                TableColumn::make()
                    ->label(__('admin.admin_menu.icon'))
                    ->name('icon')
                    ->type('flex')
                    ->justify('start')
                    ->items([
                        SvgIcon::make()->icon('${icon}')->className('mr-1 text-15px'),
                        '${icon}',
                    ]),
                TableColumn::make()->label(__('admin.admin_menu.url'))->name('url'),
                TableColumn::make()->label(__('admin.admin_menu.order'))->name('order')->quickEdit(
                    NumberControl::make()->min(0)
                ),
                TableColumn::make()->label(__('admin.admin_menu.visible'))->name('visible')->type('status'),
                TableColumn::make()->label(__('admin.admin_menu.is_home'))->name('is_home')->type('status'),
                TableColumn::make()
                    ->label(__('admin.created_at'))
                    ->name('created_at')
                    ->type('datetime')
                    ->sortable(true),
                Operation::make()->label(__('admin.actions'))->buttons([
                    $this->rowEditButton(true)->dialog(
                        Dialog::make()->title(__('admin.edit'))->body(
                            $this->form()
                                ->api($this->getUpdatePath('$id'))
                                ->initApi($this->getEditGetDataPath('$id'))
                        )->size('lg')
                    ),
                    $this->rowDeleteButton(),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()->body([
            GroupControl::make()->body([
                TextControl::make()->name('title')->label(__('admin.admin_menu.title'))->required(true),
                TextControl::make()
                    ->name('icon')
                    ->label(__('admin.admin_menu.icon'))
                    ->description(
                        __('admin.admin_menu.icon_description') .
                            '<a href="https://icones.js.org" target="_blank"> https://icones.js.org</a>'
                    ),
            ]),
            GroupControl::make()->body([
                TreeSelectControl::make()
                    ->name('parent_id')
                    ->label(__('admin.admin_menu.parent_id'))
                    ->labelField('title')
                    ->valueField('id')
                    ->value(0)
                    ->options($this->service->getTree()),
                NumberControl::make()
                    ->name('order')
                    ->label(__('admin.admin_menu.order'))
                    ->required(true)
                    ->labelRemark(__('admin.order_asc'))
                    ->displayMode('enhance')
                    ->min(0)
                    ->value(0),
            ]),
            TextControl::make()
                ->name('url')
                ->label(__('admin.admin_menu.url'))
                ->required(true)
                ->validateOnChange(true)
                ->validations(['matchRegexp' => '/^(http(s)?\:\/)?(\/)+/'])
                ->validationErrors(['matchRegexp' => __('admin.need_start_with_slash')])
                ->placeholder('eg: /admin_menus'),
            ListControl::make()
                ->name('url_type')
                ->label(__('admin.admin_menu.type'))
                ->options(AdminMenu::getType())
                ->value(AdminMenu::TYPE_ROUTE),
            SwitchControl::make()
                ->name('visible')
                ->label(__('admin.admin_menu.visible'))
                ->onText(__('admin.admin_menu.show'))
                ->offText(__('admin.admin_menu.hide'))
                ->value(1),
            SwitchControl::make()
                ->name('is_home')
                ->label(__('admin.admin_menu.is_home'))
                ->onText(__('admin.yes'))
                ->offText(__('admin.no'))
                ->description(__('admin.admin_menu.is_home_description'))
                ->value(0),
        ])->onEvent([
            'submitSucc' => [
                'actions' => [
                    'actionType' => 'custom',
                    'script'     => 'setTimeout(()=>(window.location.reload()), 1200)',
                ],
            ],
        ]);
    }

    public function detail($id): Form
    {
        return $this->baseDetail($id)->body([]);
    }

    public function quickEdit()
    {
        $data = request()->rows;

        foreach ($data as $item) {
            if (isset($item['children'])) {
                unset($item['children']);
            }

            $this->service->update($item['id'], $item);
        }

        return $this->autoResponse(true);
    }
}
