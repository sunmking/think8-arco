<?php

namespace auth;

use think\Model;
use think\facade\Db;
use think\facade\Request;
use think\db\exception\DbException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

class AuthUtil
{
    /**
     * 检测权限
     *
     * @param  null  $uid
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function checkAuth($uid = null): bool
    {
        if (!$uid) {
            return false;
        }
        $request = Request::instance();

        $m = app('http')->getName();
        $c = $request->controller();
        $a = $request->action();
        $rule_name = $m.'/'.$c.'/'.$a;

        $rule_name = strtolower($rule_name);

        $result = self::checkAuthRule($uid, $rule_name);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function checkAuthRule($userID, $route): bool
    {
        $groupAccess = Db::table(AuthTable::TB_AUTH_USER_GROUP)
            ->alias('ag')
            ->leftJoin(AuthTable::TB_AUTH_GROUP_ACCESS.' aga', 'ag.group_id=aga.group_id')
            ->field('aga.*')
            ->where([
                ['ag.uid', '=', $userID],
            ])
            ->select()->toArray();

        if (!$groupAccess) {
            return false;
        }

        $ruleIDs = array_values(array_column($groupAccess, 'rule_id'));

        $rule = Db::table(AuthTable::TB_AUTH_RULE)->field('*')->where([
            ['route', '=', $route],
            ['status', '=', 1],
        ])->find();
        if (!$rule) {
            return false;
        }
        if (!in_array($rule['id'], $ruleIDs, true)) {
            return false;
        }

        return true;
    }

    /**
     *  获取用户基础信息.
     *
     * @param array $params
     * @return null|array|Model
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getUserData(array $params = []): array|Model|null
    {
        return Db::table(AuthTable::TB_AUTH_USER)
            ->field('*')
            ->where($params)
            ->order('id', 'DESC')
            ->find();
    }
}
