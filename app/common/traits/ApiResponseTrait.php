<?php

declare(strict_types=1);

namespace app\common\traits;

use stdClass;
use think\response\Json;

trait ApiResponseTrait
{
    /**
     * @param  null  $Msg
     * @param  null  $Data
     */
    public function returnMsg(int $Code = 200, $Msg = null, $Data = null): array
    {
        return [
            'Code' => $Code,
            'Msg' => $Msg,
            'Data' => $Data ?? new stdClass(),
        ];
    }

    /**
     * 成功
     *
     * @param  null  $Msg
     * @param  null  $Data
     */
    public function jsonSuccess(int $Code = 200, $Msg = null, $Data = null): Json
    {
        return $this->jsonResponse($Code, $Msg, $Data, 'success');
    }

    /**
     * 失败
     *
     * @param  null  $Msg
     * @param  null  $Data
     */
    public function jsonFail(int $Code = 404, $Msg = null, $Data = null): Json
    {
        return $this->jsonResponse($Code, $Msg, $Data, 'fail');
    }

    /**
     * json响应
     */
    private function jsonResponse($Code, $Msg, $Data, $Status): Json
    {
        return json([
            'Status' => $Status,
            'Code' => $Code,
            'Msg' => $Msg,
            'Data' => $Data ?? null,
        ]);
    }
}
