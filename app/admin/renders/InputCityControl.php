<?php

namespace app\admin\renders;

/**
 * City 城市选择框。 文档：https://aisuda.bce.baidu.com/amis/zh-CN/components/form/city
 *
 * @method self onEvent($value) 事件动作配置
 * @method self staticClassName($value) 静态展示表单项类名
 * @method self name($value) 字段名，表单提交时的 key，支持多层级，用.连接，如： a.b.c
 * @method self horizontal($value) 当配置为水平布局的时候，用来配置具体的左右分配。
 * @method self allowStreet($value) 允许选择街道？
 * @method self loadingConfig($value) 
 * @method self placeholder($value) 占位符
 * @method self value($value) 默认值，切记只能是静态值，不支持取变量，跟数据关联是通过设置 name 属性来实现的。
 * @method self joinValues($value) 是否将各个信息拼接成字符串。
 * @method self descriptionClassName($value) 配置描述上的 className
 * @method self inline($value) 表单 control 是否为 inline 模式。
 * @method self hiddenOn($value) 是否隐藏表达式
 * @method self staticSchema($value) 
 * @method self submitOnChange($value) 当修改完的时候是否提交表单。
 * @method self validations($value) 
 * @method self staticLabelClassName($value) 静态展示表单项Label类名
 * @method self remark($value) 显示一个小图标, 鼠标放上去的时候显示提示内容
 * @method self labelRemark($value) 显示一个小图标, 鼠标放上去的时候显示提示内容, 这个小图标跟 label 在一起
 * @method self desc($value) 
 * @method self mode($value) 配置当前表单项展示模式 可选值: normal | inline | horizontal | 
 * @method self readOnlyOn($value) 只读条件
 * @method self description($value) 描述内容，支持 Html 片段。
 * @method self className($value) 容器 css 类名
 * @method self disabled($value) 是否禁用
 * @method self staticInputClassName($value) 静态展示表单项Value类名
 * @method self validationErrors($value) 验证失败的提示信息
 * @method self searchable($value) 是否显示搜索框
 * @method self extractValue($value) 开启后只会存城市的 code 信息
 * @method self visible($value) 是否显示
 * @method self static($value) 是否静态展示
 * @method self size($value) 表单项大小 可选值: xs | sm | md | lg | full | 
 * @method self labelClassName($value) 配置 label className
 * @method self inputClassName($value) 配置 input className
 * @method self required($value) 是否为必填
 * @method self clearValueOnHidden($value) 表单项隐藏时，是否在当前 Form 中删除掉该表单项值。注意同名的未隐藏的表单项值也会删掉
 * @method self delimiter($value) 拼接的符号是啥？
 * @method self disabledOn($value) 是否禁用表达式
 * @method self hidden($value) 是否隐藏
 * @method self staticPlaceholder($value) 静态展示空值占位
 * @method self label($value) 描述标题
 * @method self validateApi($value) 远端校验表单项接口
 * @method self visibleOn($value) 是否显示表达式
 * @method self labelAlign($value) 描述标题
 * @method self validateOnChange($value) 不设置时，当表单提交过后表单项每次修改都会触发重新验证， 如果设置了，则由此配置项来决定要不要每次修改都触发验证。
 * @method self type($value) 指定为城市选择框。
 * @method self readOnly($value) 是否只读
 * @method self allowCity($value) 允许选择城市？
 * @method self allowDistrict($value) 允许选择地区？
 * @method self id($value) 组件唯一 id，主要用于日志采集
 * @method self staticOn($value) 是否静态展示表达式
 * @method self labelWidth($value) label自定义宽度，默认单位为px
 * @method self hint($value) 输入提示，聚焦的时候显示
 * @method self width($value) 在Table中调整宽度
 */
class InputCityControl extends BaseRenderer
{
    public string $type = 'input-city';
}
