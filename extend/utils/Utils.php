<?php

namespace utils;

use RuntimeException;
use think\facade\Cache;
use Vtiful\Kernel\Excel;
use Vtiful\Kernel\Format;

class Utils
{
    /**
     * 生成 唯一code (Day)
     */
    public static function generateUniqueCodeByDay(string $prefix = '', string $type = 'rc', int $length = 6): string
    {
        $Redis = Cache::store('redis')->handler();
        $currentCycle = date('Ymd', time()); // 日期拼接成中间
        $key = "codegen:$currentCycle:$type"; // 生成redis健  健名前缀按照天来更新
        $codeNum = $Redis->incr($key);  // 这里用incr 方法来获取当前自增数量 incr是原子性的 能处理并发
        // 为1说明是当天的第一条，设置有效期，删除过期key
        if ($codeNum == 1) {
            // 设置有效期1天
            $expireAt = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')));
            $Redis->set($key, $codeNum, $expireAt);
            // 删除过期key，加锁，一周期只删一次 setnx锁设置键不存在则设置并返回1，否则返回0
            if ($Redis->setnx("codegen:$currentCycle:rmLock:$type", 1)) {
                $lastCycle = date('Ymd', strtotime('-1 day'));
                $keys = $Redis->keys("codegen:$lastCycle:$type");
                foreach ($keys as $k) {
                    $Redis->del($k);
                }
            }
        }

        return $prefix.$currentCycle. str_pad($codeNum, $length, '0', STR_PAD_LEFT);
    }

    /**
     * 生成 唯一code (Month)
     */
    public static function generateUniqueCodeByMonth(string $prefix = '', string $type = 'rc', int $length = 6): string
    {
        $Redis = Cache::store('redis')->handler();
        $currentCycle = date('Ym', time()); // 日期拼接成中间
        $key = "codegen:$currentCycle:$type"; // 生成redis健  健名前缀按照天来更新
        $codeNum = $Redis->incr($key);  // 这里用incr 方法来获取当前自增数量 incr是原子性的 能处理并发
        // 为1说明是当天的第一条，设置有效期，删除过期key
        if ($codeNum == 1) {
            // 设置有效期1个月
            $expireAt = strtotime(date('Y-m-01 00:00:00', strtotime('+1 month')));
            $Redis->set($key, $codeNum, $expireAt);
            // 删除过期key，加锁，一周期只删一次 setnx锁设置键不存在则设置并返回1，否则返回0
            if ($Redis->setnx("codegen:$currentCycle:rmLock:$type", 1)) {
                $lastCycle = date('Ym', strtotime('-1 month'));
                $keys = $Redis->keys("codegen:$lastCycle:$type");
                foreach ($keys as $k) {
                    $Redis->del($k);
                }
            }
        }

        return $prefix.$currentCycle. str_pad($codeNum, $length, '0', STR_PAD_LEFT);
    }

    /**
     * 生成 唯一code (Year)
     */
    public static function generateUniqueCodeByYear(string $prefix = '', string $type = 'rc', int $length = 6): string
    {
        $Redis = Cache::store('redis')->handler();
        $currentCycle = date('Y', time()); // 日期拼接成中间
        $key = "codegen:$currentCycle:$type"; // 生成redis健  健名前缀按照天来更新
        $codeNum = $Redis->incr($key);  // 这里用incr 方法来获取当前自增数量 incr是原子性的 能处理并发
        // 为1说明是当天的第一条，设置有效期，删除过期key
        if ($codeNum == 1) {
            // 设置有效期1年
            $expireAt = strtotime(date('Y-01-01 00:00:00', strtotime('+1 year')));
            $Redis->set($key, $codeNum, $expireAt);
            // 删除过期key，加锁，一周期只删一次 setnx锁设置键不存在则设置并返回1，否则返回0
            if ($Redis->setnx("codegen:$currentCycle:rmLock:$type", 1)) {
                $lastCycle = date('Y', strtotime('-1 year'));
                $keys = $Redis->keys("codegen:$lastCycle:$type");
                foreach ($keys as $k) {
                    $Redis->del($k);
                }
            }
        }

        return $prefix.$currentCycle. str_pad($codeNum, $length, '0', STR_PAD_LEFT);
    }

    /**
     * 生成订单号
     *
     * @param string|null $orderNo 订单号（可选）
     * @param string $prefix 前缀（默认为 'RK'）
     * @param int $batchNoLength 批号长度（默认为 4）
     * @return string 生成的订单号
     */
    public static function generateOrderNo(?string $orderNo = null, string $prefix = 'RK', int $batchNoLength = 4): string
    {
        // 本月，直接加一，非本月，从 00001 开始记数
        if (empty($orderNo)) {
            return $prefix.date('Ym').str_pad(1, $batchNoLength, '0', STR_PAD_LEFT);
        } else {
            if (empty($prefix)) {
                $prefix = mb_substr($orderNo, 0, 2);
            }

            // 前缀长度
            $prefixLength = mb_strlen($prefix);

            $orderNoBody = mb_substr($orderNo, $prefixLength);
            $orderNoYm = mb_substr($orderNo, $prefixLength, 6);
            $Ym = date('Ym');

            if ($Ym == $orderNoYm) {
                return $prefix.bcadd($orderNoBody, 1, 0);
            } else {
                return $prefix.$Ym.str_pad(1, $batchNoLength, '0', STR_PAD_LEFT);
            }
        }
    }


    /**
     * 获取临时文件夹
     *
     * @return false|string
     */
    public static function getTmpDir(): bool|string
    {
        // 目录可以自定义
        $tmp = ini_get('upload_tmp_dir');
        if ($tmp !== false && file_exists($tmp)) {
            return realpath($tmp);
        }

        return realpath(sys_get_temp_dir());
    }

    /**
     * 导出文件
     */
    public static function exportExcel($data, array $header = [], string $filename = 'test_tpl'): bool
    {
        error_reporting(E_ALL);
        ini_set('memory_limit', -1);
        set_time_limit(0);
        if (!is_array($data) || !$data) {
            $data = [];
        }

        $config = [
            'path' => self::getTmpDir().'/',
        ];
        $fileName = $filename.'.xlsx';

        $xlsxObject = new Excel($config);

        // Init File
        $fileObject = $xlsxObject->fileName($fileName);
        // 设置样式
        $fileHandle = $fileObject->getHandle();
        $format = new Format($fileHandle);
        $style = $format->bold()->background(
            Format::COLOR_SILVER
        )->align(Format::FORMAT_ALIGN_VERTICAL_CENTER)->toResource();
        $k = count($header);
        $cellName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'];
        // Writing data to a file ......
        $fileObjectData = $fileObject->header($header)
            ->data($data)
            ->freezePanes(1, 0)
            ->setRow('A1', 20, $style)
            ->setColumn('A1:'.$cellName[$k].'1', '14');

        // Outptu
        $filePath = $fileObjectData->output();

        // 下载
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');
        header('Content-Length: '.filesize($filePath));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        if (copy($filePath, 'php://output') === false) {
            throw new RuntimeException('导出失败');
        }

        // Delete temporary file
        @unlink($filePath);

        return true;
    }

    /**
     * @param string $type
     * @return array
     */
    public static function extracted(string $type): array
    {
        $Redis = Cache::store('redis')->handler();
        $currentCycle = date('Ymd', time()); // 日期拼接成中间
        $key = "codegen:$currentCycle:$type"; // 生成redis健  健名前缀按照天来更新
        $codeNum = $Redis->incr($key);  // 这里用incr 方法来获取当前自增数量 incr是原子性的 能处理并发
        // 为1说明是当天的第一条，设置有效期，删除过期key
        if ($codeNum == 1) {
            // 设置有效期1天
            $expireAt = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')));
            $Redis->set($key, $codeNum, $expireAt);
            // 删除过期key，加锁，一周期只删一次 setnx锁设置键不存在则设置并返回1，否则返回0
            if ($Redis->setnx("codegen:$currentCycle:rmLock:$type", 1)) {
                $lastCycle = date('Ymd', strtotime('-1 day'));
                $keys = $Redis->keys("codegen:$lastCycle:$type");
                foreach ($keys as $k) {
                    $Redis->del($k);
                }
            }
        }
        return array($currentCycle, $codeNum);
    }
}
