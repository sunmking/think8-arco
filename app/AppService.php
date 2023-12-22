<?php

declare (strict_types=1);

namespace app;

use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register(): void
    {
        // 服务注册
        // Carbon 初始化
        \Carbon\Carbon::setLocale('zh');
    }

    public function boot()
    {
        // 服务启动
    }
}
