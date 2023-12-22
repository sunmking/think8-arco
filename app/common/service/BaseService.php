<?php

declare(strict_types=1);

namespace app\common\service;

use Carbon\Carbon;
use app\common\traits\ApiResponseTrait;

class BaseService
{
    use ApiResponseTrait;

    /**
     * 所有列表的筛选条件构造
     */
    /**
     * 所有列表的筛选条件构造
     */
    public function getFilterConditions($filter): array
    {
        $filter = array_filter_empty_value($filter);
        $conditions = [];

        $filter && $conditions = $this->formatConditions($filter);

        return $this->returnMsg(200, null, $conditions);
    }

    /**
     * 构造查询条件
     */
    public function formatConditions($filter): array
    {
        if (empty($filter)) {
            return [];
        }

        $conditions = [];
        foreach ($filter as $k => $v) {
            if (mb_substr($k, -9) == 'time_from') {
                $conditions[] = [mb_substr($k, 0, -5), '>=', Carbon::parse($v)->getTimestamp()];
            } elseif (mb_substr($k, -7) == 'time_to') {
                $v = date('Y-m-d', Carbon::parse($v)->getTimestamp());
                $v .= ' 23:59:59';
                $conditions[] = [mb_substr($k, 0, -3), '<=', Carbon::parse($v)->getTimestamp()];
            } elseif (str_contains($k, 'name')) {
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, 'title')) {
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, '_no')) {
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, 'specification')) {
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, 'code')) { //编码
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, 'phone')) { // phone
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, 'mobile')) { // mobile
                $conditions[] = [$k, 'LIKE', "%{$v}%"];
            } elseif (str_contains($k, '|egt')) { // 大于等于
                $conditions[] = [mb_substr($k, 0, -4), '>=', $v];
            } elseif (str_contains($k, '|gt')) { // 大于
                $conditions[] = [mb_substr($k, 0, -3), '>', $v];
            } elseif (str_contains($k, '|elt')) { // 小于等于
                $conditions[] = [mb_substr($k, 0, -4), '<=', $v];
            } elseif (str_contains($k, '|lt')) { // 小于
                $conditions[] = [mb_substr($k, 0, -3), '<', $v];
            } elseif (str_contains($k, '|in')) { // 在列表中
                if (is_array($v)) {
                    $conditions[] = [mb_substr($k, 0, -3), 'in', $v];
                } else {
                    $val = $v;
                    if (str_contains($v, ',')) {
                        $val = array_unique(explode(',', $v) ?? []);
                    }
                    $conditions[] = [mb_substr($k, 0, -3), 'in', $val];
                }
            } else {
                $conditions[] = [$k, '=', $v];
            }
        }

        return $conditions;
    }
}
