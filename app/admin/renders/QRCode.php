<?php

namespace app\admin\renders;

/**
 * 二维码展示控件。 文档：https://aisuda.bce.baidu.com/amis/zh-CN/components/qrcode
 *
 * @method self placeholder($value) 占位符
 * @method self hiddenOn($value) 是否隐藏表达式
 * @method self static($value) 是否静态展示
 * @method self staticOn($value) 是否静态展示表达式
 * @method self staticPlaceholder($value) 静态展示空值占位
 * @method self staticInputClassName($value) 静态展示表单项Value类名
 * @method self backgroundColor($value) 背景色
 * @method self foregroundColor($value) 前景色
 * @method self qrcodeClassName($value) css 类名
 * @method self disabled($value) 是否禁用
 * @method self hidden($value) 是否隐藏
 * @method self visible($value) 是否显示
 * @method self staticClassName($value) 静态展示表单项类名
 * @method self type($value)  可选值: qrcode | qr-code | 
 * @method self name($value) 关联字段名。
 * @method self level($value) 二维码复杂级别 可选值: L | M | Q | H | 
 * @method self className($value) 容器 css 类名
 * @method self visibleOn($value) 是否显示表达式
 * @method self id($value) 组件唯一 id，主要用于日志采集
 * @method self staticSchema($value) 
 * @method self imageSettings($value) 图片配置
 * @method self disabledOn($value) 是否禁用表达式
 * @method self onEvent($value) 事件动作配置
 * @method self staticLabelClassName($value) 静态展示表单项Label类名
 * @method self codeSize($value) 二维码的宽高大小，默认 128
 */
class QRCode extends BaseRenderer
{
    public string $type = 'qrcode';
}
