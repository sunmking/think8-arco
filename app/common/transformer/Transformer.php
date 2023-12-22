<?php

declare(strict_types=1);
namespace app\common\transformer;

abstract class Transformer
{
    /**
    * 转换器
    *
    * @param  mixed  $data
    * @return mixed
    */
    abstract public function transform(mixed $data): mixed;


    /**
     * 转换器
     *
     * @param  mixed  $data
     * @return mixed
     */
    public function transformCollection(mixed $data): array
    {
        return array_map([$this, 'transform'], $data);
    }
}
