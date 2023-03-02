<?php

namespace app\admin\controller;

use support\Request;
use app\admin\SlowAdmin;
use support\Response;
use app\admin\traits\Uploader;
use app\admin\traits\QueryPath;
use app\admin\traits\PageElement;
use app\service\AdminService;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use JsonSerializable;

abstract class AdminController
{
    use QueryPath;
    use PageElement;
    use Uploader;

    protected AdminService $service;

    /** @var string $queryPath 路径 */
    protected string $queryPath;

    /** @var string|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed $adminPrefix 路由前缀 */
    protected string $adminPrefix;

    /** @var string $pageTitle 页面标题 */
    protected string $pageTitle;

    /** @var bool $isCreate 是否是新增页面 */
    protected bool $isCreate = false;

    /** @var bool $isEdit 是否是编辑页面 */
    protected bool $isEdit = false;

    public function __construct()
    {
        if (property_exists($this, 'serviceName')) {
            $this->service = $this->serviceName::make();
        }

        $this->adminPrefix = config('admin.route.prefix');
    }

    public function user()
    {
        return SlowAdmin::user();
    }

    public function actionOfGetData()
    {
        return request()->_action == 'getData';
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function getPrimaryValue($request): mixed
    {
        return $request->id;
    }

    protected function response()
    {
        return SlowAdmin::response();
    }

    protected function autoResponse($flag, $text = '')
    {
        if (!$text) {
            $text = __('admin.actions');
        }

        if ($flag) {
            return $this->response()->successMessage($text . __('admin.successfully'));
        }

        return $this->response()->fail($this->service->getError() ?? $text . __('admin.failed'));
    }

    /**
     * 获取新增页面
     *
     * @return Response|JsonSerializable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create()
    {
        $this->isCreate = true;

        $form = $this->form()->api($this->getStorePath());

        $page = $this->basePage()->body($form)->toolbar([$this->backButton()]);

        if (!$this->isTabMode()) {
            $page = $page->subTitle(__('admin.create'));
        }

        return $this->response()->success($page);
    }

    /**
     * 新增保存
     *
     * @param Request $request
     *
     * @return Response|JsonSerializable
     */
    public function store(Request $request)
    {
        return $this->autoResponse($this->service->store($request->all()), __('admin.save'));
    }

    /**
     * 详情
     *
     * @param $id
     *
     * @return Response|JsonSerializable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function show($id)
    {
        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->getDetail($id));
        }

        $page = $this->basePage()->toolbar([$this->backButton()])->body($this->detail($id));

        if (!$this->isTabMode()) {
            $page = $page->subTitle(__('admin.detail'));
        }

        return $this->response()->success($page);
    }

    /**
     * 获取编辑页面
     *
     * @param $id
     *
     * @return Response|JsonSerializable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function edit($id)
    {
        $this->isEdit = true;

        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->getEditData($id));
        }

        $form = $this->form()->api($this->getUpdatePath($id))->initApi($this->getEditGetDataPath($id));

        $page = $this->basePage()->toolbar([$this->backButton()])->body($form);

        if (!$this->isTabMode()) {
            $page = $page->subTitle(__('admin.edit'));
        }

        return $this->response()->success($page);
    }

    /**
     * 编辑保存
     *
     * @param Request $request
     *
     * @return Response|JsonSerializable
     */
    public function update(Request $request)
    {
        $result = $this->service->update($this->getPrimaryValue($request), $request->all());

        return $this->autoResponse($result, __('admin.save'));
    }

    /**
     * 删除
     *
     * @param $ids
     *
     * @return Response|JsonSerializable
     */
    public function destroy($ids)
    {
        $rows = $this->service->delete($ids);

        return $this->autoResponse($rows, __('admin.delete'));
    }
}
