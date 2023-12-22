<?php

declare(strict_types=1);

namespace app\common\service;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;

class JwtService
{
    private string $key = '96e79218965eb72c92a549dd5a330112';

    /**
     * 过期时间
     *
     * @var float|int
     */
    private int|float $expTime = 365 * 24;

    // 单例模式JwtAuth句柄
    private static ?JwtService $instance = null;

    public function __construct()
    {
    }

    /**
     * @return $this
     */
    public function setKey($key): JwtService
    {
        $this->key = $key;

        return $this;
    }

    // 获取JwtAuth的句柄
    public static function getInstance(): JwtService
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function create($data = []): string
    {
        $now = new DateTimeImmutable();
        $token = [
            'iss' => 'lw_fruit',        //签发者 可以为空
            'aud' => 'lw_fruit',          //面象的用户，可以为空
            'iat' => $now->getTimestamp(),      //签发时间
            'nbf' => $now->modify('+ 2 second')->getTimestamp(),    //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
            'exp' => $now->modify("+ {$this->expTime} hour")->getTimestamp(), //token 过期时间
            'data' => $data,
        ];
        //根据参数生成了 token
        return JWT::encode($token, $this->key, 'HS256');
    }

    public function check($jwt = ''): array
    {
        $data['Data'] = []; //数据输出
        $data['Msg'] = '请求成功';
        try {
            JWT::$leeway = 10; //当前时间减去10，把时间留点余地
            $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
            //上面这个解密很多都没有加new key()；如下面图会报其他错误
            //$decoded = JWT::decode($jwt, $key, array('HS256'));
            $arr = (array) $decoded;
            $data['Code'] = 200;
            $data['Data'] = $arr; //数据输出
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $data['Code'] = 403;
            $data['Msg'] = '签名不正确';
        } catch (\Firebase\JWT\BeforeValidException $e) {
            $data['Code'] = 4031;
            $data['Msg'] = 'TOken 还没有生效';
        } catch (\Firebase\JWT\ExpiredException $e) {
            $data['Code'] = 4032;
            $data['Msg'] = 'token过期';
        } catch (Exception $e) {
            $data['Code'] = 4033;
            $data['Msg'] = '其他错误';
        }

        return $data;
    }
}
