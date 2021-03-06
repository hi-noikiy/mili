<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/14
 * Time: 下午6:05
 */

namespace app\substation\controller;


use app\http\controller\AdminController;
use classes\substation\SubstationRechargeClass;
use classes\substation\SubstationRechargeDownClass;
use classes\system\SystemClass;
use classes\vendor\Youyunbao\YouyunbaoClass;
use think\Db;
use think\Request;

class SubstationRechargeController extends AdminController
{
    //添加订单页面
    public function getCreate()
    {
        $class = new SubstationRechargeClass();

        $result = $class->bank();

        $result['order'] = $class->order();

        return parent::view('store', $result);
    }

    //充值页面
    public function getCreate1()
    {
        $class = new SubstationRechargeClass();

        $result = $class->bank();

        $result['order'] = 's_' . time() . '_' . (SUBSTATION + 37957);

        $result['set'] = $class->master_set();

        return parent::view('store1', $result);
    }

    //添加订单
    public function postStore(Request $request)
    {
        $class = new SubstationRechargeClass();

        $class->validator_recharge($request);

        $class->recharge($request);

        return parent::success('/substation-recharge/recharge');
    }

    //添加订单
    public function postStore1(Request $request)
    {
        $class = new SubstationRechargeClass();

        $data = $class->validator_recharge1($request);
//        $data['money'] = '0.01';

        $master = $class->master();

        $class = new YouyunbaoClass($data['set']);

        $result = $class->codepay($data['money'], $data['order'], $data['type'], $master['id'], 1);

        return parent::view('youyunbao', $result);
    }

    //查看我的订单

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getRecharge()
    {
        return parent::view('recharge');
    }


    public function getRechargeTable(Request $request)
    {
        $class = new SubstationRechargeClass();

        $result = $class->recharge_index($request);

        return parent::tables($result);
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        return parent::view('index');
    }

    public function getTable(Request $request)
    {
        $class = new SubstationRechargeClass();

        $result = $class->index($request);

        return parent::tables($result);
    }

    public function getStatus(Request $request)
    {
        Db::startTrans();

        $class = new SubstationRechargeClass();

        $class->status($request);

        Db::commit();

        return parent::success('/admin/recharge/index');
    }

    /**
     * 钱包记录页面
     *
     * @return \think\response\View
     */
    public function getRecord()
    {
        $class = new SubstationRechargeClass();

        $result = [];
        $result['record_array'] = str_replace('"', "'", json_encode($class->record_array()));

        return parent::view('record', $result);
    }

    /**
     * 钱包记录列表
     *
     * @param Request $request
     * @return mixed
     */
    public function getRecordTable(Request $request)
    {
        $class = new SubstationRechargeClass();

        $result = $class->record($request);

        return parent::tables($result);
    }

    public function getDownload()
    {
        $class = new SubstationRechargeDownClass();

        //删除过期的excel文件
        $class->excel_delete();

        //生成excel
        $url = $class->excel();

        $result = [
            'status' => 'success',
            'url' => '/' . $url,
            'message' => '生成成功',
        ];


        return json($result);
    }
}