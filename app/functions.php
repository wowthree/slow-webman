<?php

use app\utils\UrlGenerator;
use support\Response;
use Illuminate\Support\Facades\Schema;

/**
 * 获取容器中的实例/获取容器
 * 有$parameters的为一次性实例，没有为单例
 * @Auther wow3ter 
 */
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return config('container');
    }
    if (empty($parameters)) {
        return config('container')->get($abstract);
    }
    return config('container')->make($abstract, $parameters);
}

function error_res($msg, $data = [], $status = 10001): Response
{
    return json(['msg' => $msg, 'data' => $data, 'status' => $status]);
}

function ok_res($data, $msg = '', $status = 0): Response
{
    return json(['msg' => $msg, 'data' => $data, 'status' => $status]);
}

/**
 * 抛出异常
 * @Auther wow3ter 
 */
function abort(int $code, string $msg = '', array $header = []): Exception
{
    throw new \Exception($msg, $code);
}

/**
 * 创建链接
 * @Auther wow3ter 
 */
function url($path = null, $parameters = [], $secure = null): string|UrlGenerator
{
    if (is_null($path)) {
        return app(UrlGenerator::class);
    }

    return app(UrlGenerator::class)->to($path, $parameters, $secure);
}

/**
 * 多语言处理函数（引用trans方法）
 * 
 * @Auther wow3ter 
 */
function __($key, $parameters = [], $local = ''): string
{
    $domain = substr($key, 0, strpos($key, '.'));
    $id = substr($key, strpos($key, '.') + 1);

    return trans($id, $parameters, $domain, $local);
}

/**
 * 加密
 * @Auther wow3ter 
 */
function bcrypt($value, $options = []): string|false|null
{
    $hash = password_hash($value, PASSWORD_BCRYPT, [
        'cost' => $this->cost($options),
    ]);
    if ($hash === false) {
        throw new RuntimeException('Bcrypt hashing not supported.');
    }
    return $hash;
}

/**
 * 检查密码
 * @Auther wow3ter 
 */
function bcrypt_check($value, $hash): bool
{
    if (strlen($hash) === 0) {
        return false;
    }

    if (password_get_info($hash)['algoName'] !== 'bcrypt') {
        throw new RuntimeException('This password does not use the Bcrypt algorithm.');
    }

    return password_verify($value, $hash);
}

function admin_path($sub = null): string
{
    return path_combine(app_path('admin'), $sub);
}

function database_path($sub): string
{
    return path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'database', $sub);
}

function admin_url($path = null): string
{
    return url(trim(config('admin.route.prefix'), '/') . '/' . trim($path, '/'));
}

/**
 * 获取表字段
 *
 * @param $tableName
 *
 * @return array
 */
function table_columns($tableName)
{
    return Schema::getColumnListing($tableName);
}

/**
 * 生成树状数据
 *
 * @param array $list
 * @param int $parentId
 *
 * @return array
 */
function array2tree(array $list, int $parentId = 0): array
{
    $data = [];
    foreach ($list as $key => $item) {
        if ($item['parent_id'] == $parentId) {
            $children = array2tree($list, (int)$item['id']);
            !empty($children) && $item['children'] = $children;
            $data[] = $item;
            unset($list[$key]);
        }
    }
    return $data;
}


function admin_resource_full_path(string $path, string $server = null)
{
    if (!$path) {
        return '';
    }

    if ($server) {
        $src = rtrim($server, '/') . '/' . ltrim($path, '/');
    }

    $src = $path;
    $scheme = config('admin.https', false) ? 'https:' : 'http:';
    return preg_replace('/^http[s]{0,1}:/', $scheme, $src, 1);
}

function amis($type = null): \app\admin\renders\Component
{
    $component = \app\admin\renders\Component::make();
    if ($type) {
        $component->setType($type);
    }
    return $component;
}
/**
 * 序列化处理
 * @Auther wow3ter 
 */
function admin_encode($str, $key = null)
{
    if (!config('app.key')) {
        throw new RuntimeException('availiable The key in `config/app` is invalid');
    }
    return base64_encode(openssl_encrypt($str, 'DES-ECB', $key ?? config('app.key')));
}

/**
 * 反序列化处理
 * @Auther wow3ter 
 */
function admin_decode($decodeStr, $key = null)
{
    if (!config('app.key')) {
        throw new RuntimeException('availiable The key in `config/app` is invalid');
    }
    $str = openssl_decrypt(base64_decode($decodeStr), 'DES-ECB', $key ?? config('app.key'));
    return $str ?: '';
}

/**
 * 处理文件上传回显问题
 * （更改上传文件位置后需要修改这里）
 * @return \Illuminate\Database\Eloquent\Casts\Attribute
 */
function file_upload_handle()
{
    return \Illuminate\Database\Eloquent\Casts\Attribute::make(
        get: fn ($value) => $value,
        set: fn ($value) => str_replace(request()->root(), '', $value)
    );
}

/**
 * 是否是json字符串
 * @param $string
 */
function is_json($string): bool
{
    if (!is_string($string)) {
        return false;
    }
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function slug(string $name, string $symbol = '-')
{
    $text = preg_replace_callback('/([A-Z])/', function ($text) use ($symbol) {
        return $symbol . strtolower($text[1]);
    }, $name);

    return str_replace('_', $symbol, ltrim($text, $symbol));
}

/**
 * 系统设置
 * @Auther wow3ter 
 */
function settings(): \app\service\AdminSettingService
{
    return \app\service\AdminSettingService::make();
}

/**
 * 拓展文件夹
 * @param string|null $path
 */
function admin_extension_path(?string $path = null): string
{
    $dir = rtrim(config('admin.extension.dir'), '/') ?: base_path('extensions');

    $path = ltrim($path, '/');

    return $path ? $dir . '/' . $path : $dir;
}
