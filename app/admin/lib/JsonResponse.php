<?php

namespace app\admin\lib;

use app\admin\renders\BaseRenderer;
use JsonSerializable;

class JsonResponse
{
    /** @var array 额外参数 */
    private array $additionalData = [
        'status'            => 0,
        'msg'               => '',
        'doNotDisplayToast' => 0,
    ];

    /**
     * @param string $message
     * @param null $data
     *
     * @return  \support\Response
     */
    public function fail(string $message = 'Service error', $data = null): \support\Response
    {
        $this->setFailMsg($message);

        return json(array_merge($this->additionalData, ['data' => $data]));
    }

    /**
     * @param null $data
     * @param string $message
     *
     * @return \support\Response
     */
    public function success($data = null, string $message = ''): \support\Response
    {
        $this->setSuccessMsg($message);

        if ($data === null) {
            $data = (object)$data;
        }

        if ($data instanceof BaseRenderer) {
            $data = $data->toArray();
        }

        return json(array_merge($this->additionalData, ['data' => $data]));
    }

    /**
     * @param string $message
     *
     * @return \support\Response
     */
    public function successMessage(string $message = ''): \support\Response
    {
        return $this->success([], $message);
    }

    private function setSuccessMsg($message)
    {
        $this->additionalData['msg'] = $message;
    }

    private function setFailMsg($message)
    {
        $this->additionalData['msg']    = $message;
        $this->additionalData['status'] = 1;
    }

    /**
     * 配置弹框时间 (ms)
     *
     * @param $timeout
     *
     * @return $this
     */
    public function setMsgTimeout($timeout): static
    {
        return $this->additional(['msgTimeout' => $timeout]);
    }

    /**
     * 添加额外参数
     *
     * @param array $params
     *
     * @return $this
     */
    public function additional(array $params = []): static
    {
        $this->additionalData = array_merge($this->additionalData, $params);

        return $this;
    }

    /**
     * 不显示弹框
     *
     * @return $this
     */
    public function doNotDisplayToast()
    {
        $this->additionalData['doNotDisplayToast'] = 1;

        return $this;
    }
}
