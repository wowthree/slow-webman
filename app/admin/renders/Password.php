<?php

namespace app\admin\renders;

/**
 * Password
 *
 * @method self staticLabelClassName($value) 静态展示表单项Label类名
 * @method self staticInputClassName($value) 静态展示表单项Value类名
 * @method self visibleOn($value) 是否显示表达式
 * @method self id($value) 组件唯一 id，主要用于日志采集
 * @method self staticClassName($value) 静态展示表单项类名
 * @method self type($value) 
 * @method self hiddenOn($value) 是否隐藏表达式
 * @method self visible($value) 是否显示
 * @method self static($value) 是否静态展示
 * @method self onEvent($value) 事件动作配置
 * @method self staticOn($value) 是否静态展示表达式
 * @method self staticSchema($value) 
 * @method self className($value) 容器 css 类名
 * @method self disabled($value) 是否禁用
 * @method self hidden($value) 是否隐藏
 * @method self mosaicText($value) 打码模式的文本
 * @method self disabledOn($value) 是否禁用表达式
 * @method self staticPlaceholder($value) 静态展示空值占位
 */
class Password extends BaseRenderer
{
    public string $type = 'password';
}