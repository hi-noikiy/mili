<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午5:41
 */

namespace app\goods\controller;

use app\http\controller\AdminController;
use classes\goods\GoodsClassClass;
use think\Request;

class GoodsClassController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new GoodsClassClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        //视图
        return parent::view('index');
    }

    /**
     * json返回列表数据
     *
     * @return \think\response\Json
     */
    public function getTable()
    {
        $result = $this->class->index();

        return parent::tables($result);
    }

    /**
     * 显示创建资源表单页
     *
     * @return \think\Response
     */
    public function getCreate()
    {
        //视图
        return parent::view('goods_class');
    }

    /**
     * 显示编辑资源表单页
     *
     * @param Request $request
     * @return \think\response\View
     */
    public function getEdit(Request $request)
    {
        //获取数据
        $result = $this->class->edit($request->get('id'));

        //视图
        return parent::view('goods_class', ['self' => $result]);
    }

    /**
     * 删除指定资源
     */
    public function getDelete()
    {
        $ids = explode(',', input('id'));

        //验证资源
        $this->class->validator_delete($ids);

        //删除
        $this->class->delete($ids);

        //反馈成功
        return parent::success('/goods_class/index');
    }

    /**
     * 保存与更新入口
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postSave(Request $request)
    {
        $id = $request->post('id');

        if (empty($id)) self::save($request);
        else self::update($id, $request);

        //反馈成功
        return parent::success('/goods_class/index');
    }

    /**
     * 保存新建的资源
     *
     * @param Request $request
     */
    private function save(Request $request)
    {
        //验证字段
        $this->class->validator_save($request);

        //添加
        $this->class->save($request);
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     * @param Request $request
     */
    private function update($id, Request $request)
    {
        //验证字段
        $this->class->validator_update($id, $request);

        //更新
        $class = $this->class->update($id, $request);

        //修改会员列表中的缓存
        $this->class->change_goods_class($class);
    }

    //批量调价页面
    public function getAmount(Request $request)
    {
        //获取数据
        $result = $this->class->edit($request->get('id'));

        //视图
        return parent::view('goods_amount', ['self' => $result]);
    }

    //批量调价页面
    public function postAmount(Request $request)
    {
        $this->class->amount($request);

        //反馈成功
        return parent::success('/goods_class/index');
    }
}