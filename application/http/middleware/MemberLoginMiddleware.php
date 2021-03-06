<?php

namespace app\http\middleware;

use app\http\exceptions\RedirectException;
use app\member\model\MemberModel;

class MemberLoginMiddleware
{
    public function handle($request, \Closure $next)
    {
        //尝试获取session中的member信息
        $member = session('member');

        //验证session中的信息格式与过期时间
        if (is_null($member) || !is_array($member) || !isset($member['id']) || !isset($member['login_ass']) || !isset($member['time']) || ($member['time'] < time())) self::errors();

        //初始化管理员模型
        $members = new MemberModel();

        //尝试获取管理员资料
        $members = $members->where('id', '=', $member['id'])->find();

        //没有获取到管理员资料，跳转至登录页面
        if (is_null($members)) self::errors();

        if ($members['status'] == '2') {

            session('member', null);
            throw new RedirectException('/login', '您的账号已经被停用');
        }

        //登录密钥验证
        if ($member['login_ass'] != $members->login_ass) self::errors();

        //更新操作时间
        $member['time'] = time() + config('young.index_login_time');

        session('member',$member);

        return $next($request);
    }

    //报错
    private function errors()
    {
        session('member',null);

        $errors = json_encode([
            'url' => '/login',
            'message' => '请登录',
        ]);

        throw new RedirectException($errors);
    }
}
