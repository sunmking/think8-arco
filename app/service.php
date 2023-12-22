<?php

use app\AppService;

// 系统服务定义文件
// 服务在完成全局初始化之后执行
return [
    // 应用服务
    AppService::class,
    // 权限服务
    tauthz\TauthzService::class,
];
