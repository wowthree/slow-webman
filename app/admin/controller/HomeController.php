<?php

namespace app\admin\controller;

use support\Response;
use app\admin\renders\Card;
use app\admin\renders\Flex;
use app\admin\renders\Html;
use app\admin\renders\Grid;
use app\admin\renders\Chart;
use app\admin\renders\Image;
use app\admin\renders\Action;
use app\admin\renders\Custom;
use app\admin\renders\Wrapper;
use JsonSerializable;

class HomeController extends AdminController
{
    protected string $queryPath = 'dashboard';

    protected string $pageTitle = '首页';

    public function index(): Response|JsonSerializable
    {
        $page = $this->basePage()->css($this->css())->body([
            Grid::make()->columns([
                $this->frameworkInfo()->md(5),
                Flex::make()->items([
                    $this->pieChart(),
                    $this->cube(),
                ]),
            ]),
            Grid::make()->columns([
                $this->lineChart()->md(8),
                Flex::make()->className('h-full')->items([
                    $this->clock(),
                    $this->giteeWidget(),
                ])->direction('column'),
            ]),
        ]);

        return $this->response()->success($page);
    }

    /**
     * gitee widget
     */
    public function giteeWidget()
    {
        return Card::make()
            ->className('h-full clear-card-mb gitee-box overflow-auto')
            ->body(
                Custom::make()->id('osc-gitee-widget-tag')->onMount(
                    <<<JS
// 隐藏 gitee-box
document.querySelector(".gitee-box").style.display = "none"

// 引入 gitee-widget
const script = document.createElement("script")
script.src = "https://gitee.com/slowlyo/slow-admin/widget_preview"
script.async = true
script.defer = true
document.body.appendChild(script)

// 隐藏元素
const hideElementByClassName = (className) => {
    const element = document.querySelector(className)
    if (element) {
        element.style.display = "none"
    }
}

setTimeout(() => {
    // 移除 gitee-box 下的 cxd-Card-body 的 padding
    const giteeBox = document.querySelector(".gitee-box")
    if (giteeBox) {
        giteeBox.querySelector(".cxd-Card-body").style.padding = "0"
    }

    // 隐藏不需要的元素
    let hideClassName = [".osc_git_info", ".osc_git_issuecommits", ".osc_git_copyright"]
    hideClassName.forEach(className =>  hideElementByClassName(className) )

    // 隐藏边框
    const oscBorderColor = document.querySelectorAll(".osc_border_color")
    oscBorderColor.forEach(element => element.style.borderColor = "transparent")

    // 显示
    giteeBox.style.display = "block"
}, 1000)
JS
                )
            );
    }

    public function clock(): Card
    {
        return Card::make()->className('h-full bg-blingbling')->header([
            'title' => '时钟',
        ])->body([
            Custom::make()
                ->name('clock')
                ->html('<div id="clock" class="text-4xl"></div><div id="clock-date" class="mt-5"></div>')
                ->onMount(
                    <<<JS
const clock = document.getElementById('clock');
const tick = () => {
    clock.innerHTML = (new Date()).toLocaleTimeString();
    requestAnimationFrame(tick);
};
tick();

const clockDate = document.getElementById('clock-date');
clockDate.innerHTML = (new Date()).toLocaleDateString();
JS

                ),
        ]);
    }

    public function frameworkInfo(): Card
    {
        return Card::make()->className('h-96')->body(
            Wrapper::make()->className('h-full')->body([
                Flex::make()->className('h-full')->direction('column')->justify('center')->items([
                    Image::make()->src(url(config('admin.logo'))),
                    Wrapper::make()->className('text-3xl mt-9')->body(config('admin.name')),
                    Flex::make()->className('w-64 mt-5')->justify('space-around')->items([
                        Action::make()
                            ->level('link')
                            ->label('Gitee')
                            ->blank(true)
                            ->actionType('url')
                            ->blank(true)
                            ->link('https://gitee.com/slowlyo/slow-admin'),
                        Action::make()
                            ->level('link')
                            ->label('SlowAdmin 文档')
                            ->blank(true)
                            ->actionType('url')
                            ->link('https://slowlyo.gitee.io/slow-admin-doc/'),
                        Action::make()
                            ->level('link')
                            ->label('Amis 文档')
                            ->blank(true)
                            ->actionType('url')
                            ->link('https://aisuda.bce.baidu.com/amis/zh-CN/docs/index'),
                    ]),
                ]),
            ])
        );
    }

    public function pieChart(): Card
    {
        return Card::make()->className('h-96')->body(
            Chart::make()->height(350)->config("{
  tooltip: { trigger: 'item' },
  legend: { bottom: 0, left: 'center' },
  series: [
    {
      name: 'Access From',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
      label: { show: false, position: 'center' },
      emphasis: {
        label: { show: true, fontSize: '40', fontWeight: 'bold' }
      },
      labelLine: { show: false },
      data: [
        { value: 1048, name: 'Search Engine' },
        { value: 735, name: 'Direct' },
        { value: 580, name: 'Email' },
        { value: 484, name: 'Union Ads' },
        { value: 300, name: 'Video Ads' }
      ]
    }
  ]
}")
        );
    }

    public function lineChart(): Card
    {
        $randArr = function () {
            $_arr = [];
            for ($i = 0; $i < 7; $i++) {
                $_arr[] = random_int(10, 200);
            }
            return '[' . implode(',', $_arr) . ']';
        };

        $random1 = $randArr();
        $random2 = $randArr();

        $chart = Chart::make()->height(380)->className('h-96')->config("{
title:{ text: '会员增长情况', },
tooltip: { trigger: 'axis' },
xAxis: { type: 'category', boundaryGap: false, data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] },
yAxis: { type: 'value' },
grid:{ left: '7%', right:'3%', top: 60, bottom: 30, },
legend: { data: ['访问量','注册量'] },
series: [
    { name: '访问量', data: {$random1}, type: 'line', areaStyle: {}, smooth: true, symbol: 'none', },
    { name:'注册量', data: {$random2}, type: 'line', areaStyle: {}, smooth: true, symbol: 'none', },
]}");

        return Card::make()->className('clear-card-mb')->body($chart);
    }

    public function cube(): Card
    {
        return Card::make()->className('h-96 ml-4 w-8/12')->body(
            Html::make()->html(
                <<<HTML
<style>
    .cube-box{ height: 300px; display: flex; align-items: center; justify-content: center; }
  .cube { width: 100px; height: 100px; position: relative; transform-style: preserve-3d; animation: rotate 10s linear infinite; }
  .cube:after {
    content: '';
    width: 100%;
    height: 100%;
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.2);
    position: absolute;
    transform-origin: bottom;
    transform-style: preserve-3d;
    transform: rotateX(90deg) translateY(50px) translateZ(-50px);
    background-color: rgba(0, 0, 0, 0.1);
  }
  .cube div {
    background-color: rgba(64, 158, 255, 0.7);
    position: absolute;
    width: 100%;
    height: 100%;
    border: 1px solid rgb(27, 99, 170);
    box-shadow: 0 0 60px rgba(64, 158, 255, 0.7);
  }
  .cube div:nth-child(1) { transform: translateZ(-50px); animation: shade 10s -5s linear infinite; }
  .cube div:nth-child(2) { transform: translateZ(50px) rotateY(180deg); animation: shade 10s linear infinite; }
  .cube div:nth-child(3) { transform-origin: right; transform: translateZ(50px) rotateY(270deg); animation: shade 10s -2.5s linear infinite; }
  .cube div:nth-child(4) { transform-origin: left; transform: translateZ(50px) rotateY(90deg); animation: shade 10s -7.5s linear infinite; }
  .cube div:nth-child(5) { transform-origin: bottom; transform: translateZ(50px) rotateX(90deg); background-color: rgba(0, 0, 0, 0.7); }
  .cube div:nth-child(6) { transform-origin: top; transform: translateZ(50px) rotateX(270deg); }

  @keyframes rotate {
    0% { transform: rotateX(-15deg) rotateY(0deg); }
    100% { transform: rotateX(-15deg) rotateY(360deg); }
  }
  @keyframes shade { 50% { background-color: rgba(0, 0, 0, 0.7); } }
</style>
<div class="cube-box">
    <div class="cube">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
HTML

            )
        );
    }

    private function css(): array
    {
        return [
            '.amis-scope .clear-card-mb'                 => [
                'margin-bottom' => '0 !important',
            ],
            '.amis-scope .cxd-Image'                     => [
                'border' => '0',
            ],
            '.amis-scope .bg-blingbling'                 => [
                'color'             => '#fff',
                'background'        => 'linear-gradient(to bottom right, #2C3E50, #FD746C, #FF8235, #ffff1c, #92FE9D, #00C9FF, #a044ff, #e73827)',
                'background-repeat' => 'no-repeat',
                'background-size'   => '1000% 1000%',
                'animation'         => 'gradient 60s ease infinite',
            ],
            '@keyframes gradient'                        => [
                '0%{background-position:0% 0%}
                  50%{background-position:100% 100%}
                  100%{background-position:0% 0%}',
            ],
            '.amis-scope .bg-blingbling .cxd-Card-title' => [
                'color' => '#fff',
            ],
        ];
    }
}
