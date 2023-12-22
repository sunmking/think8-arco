<?php

namespace utils;

class PHPTree
{
    protected static $config = [
        // 主键
        'primary_key' => 'id',
        // 父键
        'parent_key' => 'pid',
        // 展开属性
        'expanded_key' => 'expanded',
        // 叶子节点属性
        'leaf_key' => 'leaf',
        // 孩子节点属性
        'children_key' => 'children',
        // 是否展开子节点
        'expanded' => false,
    ];

    // 结果集
    protected static $result = [];

    // 层次暂存
    protected static $level = [];

    /**
     * @name 生成树形结构
     *
     * @param array 二维数组
     * @param  mixed  $data
     * @param  mixed  $options
     * @return mixed 多维数组
     */
    public static function makeTree($data, $options = [])
    {
        $dataset = self::buildData($data, $options);

        return self::makeTreeCore(0, $dataset, 'normal');
    }

    /**
     * 生成线性结构, 便于HTML输出, 参数同上.
     *
     * @param  array  $options
     * @return array
     */
    public static function makeTreeForHtml($data, $options = [])
    {
        $dataset = self::buildData($data, $options);

        return self::makeTreeCore(0, $dataset, 'linear');
    }

    // 格式化数据, 私有方法
    private static function buildData($data, $options)
    {
        $config = array_merge(self::$config, $options);
        self::$config = $config;
        extract($config);

        $r = [];
        foreach ($data as $item) {
            $id = $item[$primary_key];
            $parent_id = $item[$parent_key];
            $r[$parent_id][$id] = $item;
        }

        return $r;
    }

    // 生成树核心, 私有方法
    private static function makeTreeCore($index, $data, $type = 'linear')
    {
        extract(self::$config);
        foreach ($data[$index] as $id => $item) {
            if ('normal' == $type) {
                if (isset($data[$id])) {
                    $item[$expanded_key] = self::$config['expanded'];
                    $item[$children_key] = self::makeTreeCore($id, $data, $type);
                } else {
                    $item[$leaf_key] = true;
                }
                $r[] = $item;
            } elseif ('linear' === $type) {
                $parent_id = $item[$parent_key];
                self::$level[$id] = 0 === $index ? 0 : self::$level[$parent_id] + 1;
                $item['level'] = self::$level[$id];
                self::$result[] = $item;

                if (isset($data[$id])) {
                    self::makeTreeCore($id, $data, $type);
                }

                $r = self::$result;
            }
        }

        return $r;
    }
}
