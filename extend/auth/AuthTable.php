<?php

namespace auth;

class AuthTable
{
    public const TB_AUTH_USER = 'tb_auth_user'; //系统用户表

    public const TB_AUTH_RULE = 'tb_auth_rule'; //功能表

    public const TB_AUTH_GROUP = 'tb_auth_group'; //用户组表

    public const TB_AUTH_USER_GROUP = 'tb_auth_user_group'; //用户所属权限表

    public const TB_AUTH_GROUP_ACCESS = 'tb_auth_group_access'; //用户组关联权限表

    public const TB_AUTH_DEPARTMENT = 'tb_auth_department'; //部门表

    public const TB_AUTH_POSITION = 'tb_auth_position'; //职位表

    public const TB_AUTH_LOGIN_LOG = 'tb_auth_login_log'; //登录日志表

    public const TB_AUTH_ACCESS_LOG = 'tb_auth_access_log'; //登录access

    public const TB_AUTH_COMPANY = 'tb_auth_company'; //公司表

    public const TB_AUTH_USER_COMPANY = 'tb_auth_user_company'; //用户公司
}
