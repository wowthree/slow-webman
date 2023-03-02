<?php

namespace app\admin\controller;

use support\Request;
use app\admin\SlowAdmin;
use app\admin\lib\Captcha;
use app\admin\renders\Page;
use Respect\Validation\Validator as v;
use app\admin\renders\Form;
use app\admin\renders\TextControl;
use app\admin\renders\ImageControl;
use app\service\AdminUserService;
use Shopwwi\WebmanAuth\Facade\Auth;

class AuthController extends AdminController
{
    protected string $serviceName = AdminUserService::class;

    public function login(Request $request)
    {
        if (config('admin.auth.login_captcha')) {
            if (!$request->input('captcha')) {
                return $this->response()->fail(__('admin.required', ['attribute' => __('admin.captcha')]));
            }

            if (strtolower(admin_decode($request->sys_captcha)) != strtolower($request->captcha)) {
                return $this->response()->fail(__('admin.captcha_error'));
            }
        }

        try {
            $data = v::input($request->all(), [
                'username' => v::notEmpty()->setName('用户名'),
                'password' => v::notEmpty()->setName('密码'),
            ]);

            if ($tokenObj = Auth::guard('admin')->attempt($data)) {
                $token = $tokenObj->access_token;
                return $this->response()->success(compact('token'), __('admin.login_successful'));
            }

            abort(400, __('admin.login_failed'));
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }

    /**
     * 刷新验证码
     *
     * @return \support\Response|\JsonSerializable
     */
    public function reloadCaptcha()
    {
        $captcha = new Captcha();

        $captcha_img = $captcha->showImg();
        $sys_captcha = admin_encode($captcha->getCaptcha());

        return $this->response()->success(compact('captcha_img', 'sys_captcha'));
    }

    public function logout(): \support\Response|\JsonSerializable
    {
        $this->guard()->logout();

        return $this->response()->successMessage();
    }

    protected function guard(): \Shopwwi\WebmanAuth\Auth
    {
        return SlowAdmin::guard();
    }

    public function currentUser()
    {
        return $this->response()->success(SlowAdmin::user()->only(['id', 'name', 'avatar']));
    }

    public function userSetting(): \support\Response|\JsonSerializable
    {
        $user = $this->user()->makeHidden([
            'username',
            'password',
            'remember_token',
            'created_at',
            'updated_at',
            'roles',
        ]);

        $form = Form::make()
            ->title('')
            ->panelClassName('px-48 m:px-0')
            ->mode('horizontal')
            ->data($user)
            ->api('put:' . admin_url('/user_setting/' . $user->id))
            ->body([
                ImageControl::make()
                    ->label(__('admin.admin_user.avatar'))
                    ->name('avatar')
                    ->receiver($this->uploadImagePath()),
                TextControl::make()->label(__('admin.admin_user.name'))->name('name')->required(true),
                TextControl::make()->type('input-password')->label(__('admin.old_password'))->name('old_password'),
                TextControl::make()->type('input-password')->label(__('admin.password'))->name('password'),
                TextControl::make()
                    ->type('input-password')
                    ->label(__('admin.confirm_password'))
                    ->name('confirm_password'),
            ]);

        $page = Page::make()->body($form);

        if (!$this->isTabMode()) {
            $page = $page->title(__('admin.user_setting'));
        }

        return $this->response()->success($page);
    }

    public function saveUserSetting($id): \support\Response|\JsonSerializable
    {
        $result = $this->service->updateUserSetting(
            $id,
            request()->only([
                'avatar',
                'name',
                'old_password',
                'password',
                'confirm_password',
            ])
        );

        return $this->autoResponse($result);
    }
}
