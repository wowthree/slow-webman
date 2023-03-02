<?php

namespace app\admin\controller;

use support\Response;
use app\admin\renders\Page;
use app\admin\renders\Form;
use app\admin\renders\Operation;
use app\admin\renders\TableColumn;
use app\admin\renders\TextControl;
use app\service\AdminRoleService;
use JsonSerializable;
use app\admin\renders\TreeSelectControl;
use app\service\AdminPermissionService;

class AdminRoleController extends AdminController
{
    protected string $serviceName = AdminRoleService::class;

    protected string $queryPath = 'system/admin_roles';

    protected string $pageTitle;

    public function __construct()
    {
        $this->pageTitle = __('admin.admin_roles');

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
            ->headerToolbar([
                $this->createButton(true),
                'bulkActions',
                amis('reload')->align('right'),
                amis('filter-toggler')->align('right'),
            ])
            ->filterTogglable(false)
            ->columns([
                TableColumn::make()->label('ID')->name('id')->sortable(true),
                TableColumn::make()->label(__('admin.admin_role.name'))->name('name'),
                TableColumn::make()->label(__('admin.admin_role.slug'))->name('slug')->type('tag'),
                TableColumn::make()->label(__('admin.created_at'))->name('created_at')->type('datetime')->sortable(true),
                TableColumn::make()->label(__('admin.updated_at'))->name('updated_at')->type('datetime')->sortable(true),
                Operation::make()->label(__('admin.actions'))->buttons([
                    $this->rowEditButton(true),
                    $this->rowDeleteButton()->visibleOn('${slug != "administrator"}'),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()->body([
            TextControl::make()->label(__('admin.admin_role.name'))->name('name')->required(true),
            TextControl::make()
                ->label(__('admin.admin_role.slug'))
                ->name('slug')
                ->description(__('admin.admin_role.slug_description'))
                ->required(true),
            TreeSelectControl::make()
                ->name('permissions')
                ->label(__('admin.admin_role.permissions'))
                ->multiple(true)
                ->options(AdminPermissionService::make()->getTree())
                ->searchable(true)
                ->labelField('name')
                ->valueField('id')
                ->autoCheckChildren(false)
                ->joinValues(false)
                ->extractValue(true),
        ]);
    }

    public function detail($id): Form
    {
        return $this->baseDetail($id)->body([]);
    }
}
