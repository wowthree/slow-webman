<?php

namespace app\service;

use Illuminate\Support\Arr;
use app\admin\Admin;
use support\DB;
use support\Cache;
use app\model\AdminSetting;

class AdminSettingService extends AdminService
{
    protected string $modelName = AdminSetting::class;

    protected string $cacheKeyPrefix = 'app_setting_';

    /**
     * 保存设置
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function set($key, $value = null)
    {
        try {
            $setting = $this->query()->firstOrNew(['key' => $key]);

            $setting->values = $value;
            $this->clearCache($key);
            $setting->save();
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 批量保存设置
     *
     * @param array $data
     *
     * @return bool
     */
    public function setMany(array $data)
    {
        DB::beginTransaction();
        try {
            foreach ($data as $key => $value) {
                if (!$this->set($key, $value)) {
                    throw new \Exception($this->getError());
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 批量保存设置项并返回后台响应格式数据
     *
     * @param array $data
     *
     * @return \support\JsonResponse|\JsonSerializable
     */
    public function adminSetMany(array $data)
    {
        $prefix = __('admin.save');

        if ($this->setMany($data)) {
            return Admin::response()->successMessage($prefix . __('admin.successfully'));
        }

        return Admin::response()->fail($prefix . __('admin.failed'), $this->getError());
    }

    /**
     * 以数组形式返回所有设置
     *
     * @return array
     */
    public function all()
    {
        return $this->query()->pluck('values', 'key')->toArray();
    }

    /**
     * 获取设置项
     *
     * @param string $key 设置项key
     * @param mixed|null $default 默认值
     * @param bool $fresh 是否直接从数据库获取
     *
     * @return mixed|null
     */
    public function get(string $key, mixed $default = null, bool $fresh = false)
    {
        if ($fresh) {
            return $this->query()->where('key', $key)->value('values') ?? $default;
        }

        $cache_key = $this->getCacheKey($key);

        if (!$value = Cache::get($cache_key)) {
            $value = $this->query()->where('key', $key)->value('values');
            Cache::set($cache_key, $value);
        }

        return $value ?? $default;
    }

    /**
     * 获取设置项中的某个值
     *
     * @param string $key 设置项key
     * @param string $path 通过点号分隔的路径, 同Arr::get()
     * @param $default
     *
     * @return array|\ArrayAccess|mixed|null
     */
    public function arrayGet(string $key, string $path, $default = null)
    {
        $value = $this->get($key);

        if (is_array($value)) {
            return Arr::get($value, $path, $default);
        }

        return $default;
    }

    /**
     * 清除指定设置项的缓存
     *
     * @param $key
     *
     * @return void
     */
    public function clearCache($key)
    {
        Cache::delete($this->getCacheKey($key));
    }

    public function getCacheKey($key)
    {
        return $this->cacheKeyPrefix . $key;
    }
}
