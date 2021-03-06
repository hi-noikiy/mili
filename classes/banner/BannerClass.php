<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/22
 * Time: 下午3:51
 */

namespace classes\banner;


use app\banner\model\BannerImagesModel;
use app\banner\model\BannerModel;
use classes\AdminClass;
use classes\ListInterface;
use classes\vendor\StorageClass;
use think\Request;

class BannerClass extends AdminClass implements ListInterface
{
    public $model;
    public $image;
    private $dir = 'banner_image';
    public $number = 5;

    public function __construct()
    {
        $this->model = new BannerModel();
        $this->image = new BannerImagesModel();
        if (!is_dir($this->dir)) mkdir($this->dir);//新建文件夹
    }

    public function index()
    {
        $where = [
            //['substation', '=', SUBSTATION]
        ];

        $other = [
            'order_name' => 'sort',
            'where' => $where,
            'substation' => '1',
        ];

        $result = parent::page($this->model, $other);

        foreach ($result['message'] as &$v) {

            if (is_null($v['location'])) $v['location'] = config('young.image_not_found');
        }

        return $result;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function save(Request $request)
    {
        $image = $this->image->where('id', $request->post('imageId'))->find();
        if (is_null($image)) parent::ajax_exception(000, '请重新上传图片');

        $model = $this->model;
        $model->title = $request->post('title');
        $model->image = $image->id;
        $model->location = $image->location;
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->link = $request->post('link');
        $model->substation = SUBSTATION;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        $image->pid = $model->id;
        $image->save();
    }

    public function read($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/banner/index', 'banner不存在');

        if (is_null($model->location)) $model->location = config('young.image_not_found');

        return $model->getData();
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $image = $this->image->where('id', $request->post('imageId'))->find();
        if (is_null($image)) parent::ajax_exception(000, '请重新上传图片');

        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::ajax_exception(000, 'banner不存在');

        $model->title = $request->post('title');
        $model->image = $image->id;
        $model->location = $image->location;
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->link = $request->post('link');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();

        $image->pid = $model->id;
        $image->save();

        $images = new BannerImagesModel();
        $images->where('pid', '=', $model->id)->where('id', '<>', $image->id)->update(['pid' => null]);
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
        $this->image->whereIn('pid', $id)->update(['pid' => null]);
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'title|描述' => 'require|min:1|max:255',
            'show|显示' => 'require',
            'sort|排序' => 'require|integer|between:1,999',
            'imageId|图片' => 'require',
            'link|链接' => 'require|min:1|max:255',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('show') == 'on') {

            $model = new BannerModel();
            $test = $model->where('show', '=', 'on')->where('substation', '=', SUBSTATION)->count();
            if ($test >= $this->number) parent::ajax_exception(000, '至多只能显示『' . $this->number . '』个banner');
        }
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'title|描述' => 'require|min:1|max:255',
            'show|显示' => 'require',
            'sort|排序' => 'require|integer|between:1,999',
            'imageId|图片' => 'require',
            'link|链接' => 'require|min:1|max:255',
        ];

        $message = [
            'imageId.unique' => '不能使用其他banner中的图片'
        ];

        $result = parent::validator($request->post(), $rule, $message);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('show') == 'on') {

            $model = new BannerModel();
            $test = $model->where('show', '=', 'on')->where('id', '<>', $id)->where('substation', '=', SUBSTATION)->count();
            if ($test >= $this->number) parent::ajax_exception(000, '至多只能显示『' . $this->number . '』个banner');
        }
    }

    public function validator_delete($id)
    {
        // TODO: Implement validator_delete() method.
    }

    public function image(Request $request)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('images');

        $location = 'banner_' . ($request->post('id') ? $request->post('id') : time());

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($this->dir, $location);

        // 上传失败获取错误信息
        if (!$info) parent::ajax_exception(000, $file->getError());

        $model = $this->image;
        $model->location = '/' . $this->dir . '/' . $info->getSaveName();
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        return [
            'image' => $model->location,
            'imageId' => $model->id,
        ];
    }

    //过期文件删除
    public function image_delete()
    {
        //过期时间
        $date = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';

        //验证今天是否执行过删除
        $storage = new StorageClass('banner_image_delete');
        $over = $storage->get();
        if (!is_array($over) && ($over >= $date)) return;//执行过

        //寻找并删除文件
        $model = new BannerImagesModel();
        $result = $model->where('created_at', '<', $date)->where('pid', '=', null)->select();
        if (count($result) > 0) foreach ($result as $v) {

            if (!is_null($v->location) && file_exists(substr($v->location, 1))) unlink(substr($v->location, 1));
        }

        //删除数据
        $model = new BannerImagesModel();
        $model->where('created_at', '<', $date)->where('pid', null)->delete();

        //保存删除时间
        $storage->save($date);
    }
}