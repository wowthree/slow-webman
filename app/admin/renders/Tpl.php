<?php

namespace app\admin\renders;

/**
 * tpl 渲染器
 *
 * @method self id($value) 组件唯一 id，主要用于日志采集
 * @method self static($value) 是否静态展示
 * @method self staticClassName($value) 静态展示表单项类名
 * @method self raw($value) 
 * @method self inline($value) 是否内联显示？
 * @method self wrapperComponent($value) 标签类型
 * @method self disabled($value) 是否禁用
 * @method self badge($value) 角标
 * @method self onEvent($value) 事件动作配置
 * @method self staticOn($value) 是否静态展示表达式
 * @method self staticPlaceholder($value) 静态展示空值占位
 * @method self staticLabelClassName($value) 静态展示表单项Label类名
 * @method self staticInputClassName($value) 静态展示表单项Value类名
 * @method self hidden($value) 是否隐藏
 * @method self visible($value) 是否显示
 * @method self staticSchema($value) 
 * @method self type($value) 指定为模板渲染器。文档：https://aisuda.bce.baidu.com/amis/zh-CN/concepts/template 可选值: tpl | html | 
 * @method self className($value) 容器 css 类名
 * @method self disabledOn($value) 是否禁用表达式
 * @method self tpl($value) 
 * @method self html($value) 
 * @method self text($value) 
 * @method self style($value) 自定义样式
 * @method self hiddenOn($value) 是否隐藏表达式
 * @method self visibleOn($value) 是否显示表达式
 */
class Tpl extends BaseRenderer
{
    public string $type = 'tpl';
}
