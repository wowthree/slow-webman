<?php

namespace app\service;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use app\model\AdminPermission;

class AdminPermissionService extends AdminService
{
    protected string $modelName = AdminPermission::class;

    public function getTree(): array
    {
        $list = $this->query()->orderByDesc('order')->get()->toArray();

        return array2tree($list);
    }

    public function parentIsChild($id, $parent_id): bool
    {
        $parent = $this->query()->find($parent_id);

        do {
            if ($parent->parent_id == $id) {
                return true;
            }
            // 如果没有parent 则为顶级 退出循环
            $parent = $parent->parent;
        } while ($parent);

        return false;
    }

    public function getEditData($id): Model|\Illuminate\Database\Eloquent\Collection|Builder|array|null
    {
        $permission = parent::getEditData($id);

        $permission->load(['menus']);

        return $permission;
    }

    public function store($data): bool
    {
        if ($this->hasRepeated($data)) {
            return false;
        }

        $model = $this->getModel();

        $menus = Arr::pull($data, 'menus');

        foreach ($data as $k => $v) {
            $model->setAttribute($k, $v);
        }

        if ($model->save()) {
            $model->menus()->sync(Arr::has($menus, '0.id') ? Arr::pluck($menus, 'id') : $menus);

            return true;
        }

        return false;
    }

    public function update($primaryKey, $data): bool
    {
        if ($this->hasRepeated($data, $primaryKey)) {
            return false;
        }

        $parent_id = Arr::get($data, 'parent_id');
        if ($parent_id != 0) {
            if ($this->parentIsChild($primaryKey, $parent_id)) {
                $this->setError('父级不允许设置为当前子权限');
                return false;
            }
        }

        $model = $this->query()->whereKey($primaryKey)->first();

        $menus = Arr::pull($data, 'menus');

        foreach ($data as $k => $v) {
            $model->setAttribute($k, $v);
        }

        if ($model->save()) {
            $model->menus()->sync(Arr::has($menus, '0.id') ? Arr::pluck($menus, 'id') : $menus);

            return true;
        }

        return false;
    }

    public function hasRepeated($data, $id = 0): bool
    {
        $query = $this->query()->when($id, fn ($query) => $query->where('id', '<>', $id));

        if ((clone $query)->where('name', $data['name'])->exists()) {
            $this->setError('权限名称重复');
            return true;
        }

        if ((clone $query)->where('slug', $data['slug'])->exists()) {
            $this->setError('权限标识重复');
            return true;
        }

        return false;
    }

    public function list()
    {
        return ['items' => $this->getTree()];
    }
}
