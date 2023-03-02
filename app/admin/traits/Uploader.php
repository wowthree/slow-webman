<?php

namespace app\admin\traits;

use Illuminate\Support\Str;

trait Uploader
{
    /**
     * 图片上传路径
     *
     * @return string|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Contracts\Foundation\Application
     */
    public function uploadImagePath(): string|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Contracts\Foundation\Application
    {
        return admin_url('upload_image');
    }

    public function uploadImage(): \support\Response|\JsonSerializable
    {
        return $this->upload('image');
    }

    /**
     * 文件上传路径
     *
     * @return string|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Contracts\Foundation\Application
     */
    public function uploadFilePath(): string|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Contracts\Foundation\Application
    {
        return admin_url('upload_file');
    }

    public function uploadFile()
    {
        return $this->upload();
    }

    /**
     * 富文本编辑器上传路径
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function uploadRichPath()
    {
        return admin_url('upload_rich');
    }

    public function uploadRich()
    {
        $file = request()->file('file');

        if (!$file || !$file->isValid()) {
            return $this->response()->fail(__('admin.upload_file_error'));
        }
        $path = $this->storeUpload($file, config('admin.upload.directory.rich'), $file->getUploadExtension());
        return $this->response()->additional(compact('path'))->success(compact('path'));
    }

    protected function upload($type = 'file')
    {
        $file = request()->file('file');

        if (!$file || !$file->isValid()) {
            return $this->response()->fail(__('admin.upload_file_error'));
        }

        $path = $this->storeUpload($file, config('admin.upload.directory.' . $type), $file->getUploadExtension());

        return $this->response()->success(['value' => $path]);
    }

    /**
     * 保存上传文件
     * @param \Webman\Http\UploadFile $file
     * @Auther wow3ter 
     */
    public function storeUpload($file, $subDir, $ext)
    {
        $path = public_path(config('admin.upload.disk')) . '/' . $subDir;
        $file_name = Str::random(40) . '.' . $ext;
        $full_path = $path . '/' . $file_name;
        $file->move($full_path);
        return str_replace(public_path(), '', $full_path);
    }
}
