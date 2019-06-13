<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午5:54
 */

namespace app\index\controller;


use classes\index\RechargeClass;
use classes\system\BankClass;
use classes\vendor\Youyunbao\YouyunbaoClass;
use think\Request;

class RechargeController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new RechargeClass();
    }

    //充值页面
    public function getRecharge()
    {
//        $class = new BankClass();

        $result = [
            'choice' => '/recharge',
            'order' => time(),//$this->class->order(),
//            'bank' => $class->index(),
        ];

        return parent::view('recharge', $result);
    }

    //生成充值订单
    public function postRecharge(Request $request)
    {
        $this->class->status();

        $data = $this->class->validator_recharge($request);

        $class = new YouyunbaoClass();

        $result = $class->codepay($data['money'], $data['order'], $data['type']);
//        $this->class->recharge($request);

        return parent::view('youyunbao', $result);
//        return parent::success('/', '下单成功，点击前往支付');
    }

    //充值记录页面
    public function getNote()
    {
        $result = [
            'choice' => '/recharge-note'
        ];

        return parent::view('recharge-note', $result);
    }

    //充值记录数据
    public function getNoteTable()
    {
        $result = $this->class->note();

        return parent::tables($result);
    }

    //取消订单
    public function getBack()
    {
        $this->class->status();

        $this->class->rollback();

        return parent::success();
    }

    //余额记录页面
    public function getExpense()
    {
        $result = [
            'choice' => '/expense-note'
        ];

        return parent::view('expense-note', $result);
    }

    //余额记录数据
    public function getExpenseTable()
    {
        $result = $this->class->expense();

        return parent::tables($result);
    }
}