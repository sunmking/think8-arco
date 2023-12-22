<?php

namespace app\common\traits;

use think\facade\Db;

trait ModelTrait
{
    public function getAllTableFields(): array
    {
        return Db::getTableFields($this->table);
    }
}