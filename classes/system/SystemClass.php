<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午5:30
 */

namespace classes\system;

use app\substation\model\SubstationModel;
use classes\AdminClass;
use classes\vendor\StorageClass;
use think\Request;

class SystemClass extends AdminClass
{
    public $storage;
    public $dir = 'logo';

    public function __construct()
    {
        $name = 'sysSetting.txt';

        if (defined('SUBSTATION')) {

            if (!empty(SUBSTATION)) {

                $name = 'sysSetting_' . SUBSTATION . '.txt';
                $this->dir .= '_' . SUBSTATION;
            }
        } else {

            //安全路径
            $safe_path = include 'safe_path.php';

            //获取访问域名
            $localhost = $_SERVER['SERVER_NAME'];

            if (!in_array($localhost, $safe_path)) {

                //该域名不是主站域名，寻找站点id
                $substation_model = new SubstationModel();
                $substation_model = $substation_model->where('localhost', '=', $localhost)->where('status', '=', 'on')->find();

                //没找到该分站或者该分站与管理员分站不符
                if (is_null($substation_model)) exit('你无权登录此站点');
            }
        }

        $this->storage = new StorageClass($name);
        if (!is_dir($this->dir)) mkdir($this->dir);
    }

    public function index()
    {
        //读取设定文件
        $set = $this->storage->get();

        //获取默认配置
        $result = self::defaults();

        //设定文件存在，修改返回配置
        if (!is_array($set)) {

            //格式化配置信息
            $set = json_decode($set, true);

            //循环设定数据
            foreach ($result as $k => &$v) {

                //设定文件中有的设定，修改之
                if (isset($set[$k])) $v = $set[$k];
            }
        }

        //返回设定文件
        return $result;
    }

    //保存配置文件
    public function save()
    {
        //获取提交的参数
        $set = input();

        //单独处理富文本
//        $set['content'] = $set['fwb-content'];
//        unset($set['fwb-content']);

        //获取原始配置
        $result = self::defaults();

        //循环修改
        foreach ($result as $k => &$v) {

            //设定文件中有的设定，修改之
            if (isset($set[$k])) {

                $v = $set[$k];
            }
        }

        //保存到文件
        $this->storage->save(json_encode($result));

        return $result;
    }

    //验证
    public function save_validator()
    {
        $rule = [
            'webName|网站名称' => 'require|min:1|max:100',
            'webTitle|网站title' => 'require|min:1|max:100',
            'webKeyword|关键字' => 'require|min:1|max:100',
            'webDesc|网站描述' => 'require|min:1|max:100',
            'webSwitch|网站开关' => 'require',
            'webCloseReason|关闭原因' => 'requireIf:webSwitch,off',
            'fwb-content|下单提示' => 'require|min:1|max:20000',
            'logo|网站LOGO' => 'require',
            'webCopyright|版权信息' => 'require|max:10000',
            'userCommiss|佣金比例' => 'require|integer|between:0,10000',
            'rechargeBase|充值基数' => 'require|integer|between:0,100000000',
            'rechargeTimes|充值倍数' => 'require|integer|between:0,100000000',
            'rechargeSwitch|充值开关' => 'require|in:on,off',
            'rechargeGradeSwitch|充值送会员' => 'require|in:on,off',
//            'rechargeMan|收款人姓名' => 'require|max:50',
//            'rechargeNo|收款人卡号' => 'require|max:50',
//            'rechargeBank|收款人银行' => 'require|max:50',
//            'rechargeAddress|收款开户行' => 'require|max:50',
//            'rechargeNote|注意事项' => 'require|max:50',
//            'rechargeAudit|审核提示' => 'require|max:50',
            'login|登录图片' => 'require',
            'loginUrl|登录图片链接' => 'require|length:0,255',
            'reg|注册图片' => 'require',
            'regUrl|注册图片连接' => 'require|length:0,255',
            'goods_number|商品数量提示' => 'max:20000',
            'self_default|完善资料提示' => 'max:20000',
            'withdraw|佣金管理提示' => 'max:20000',
            'loginReason|图片描述' => 'max:20000',
//            'jushuitanShopid|聚水潭店铺id' => 'require|max:20',
//            'jushuitanId|聚水潭id' => 'require|max:50',
//            'jushuitanKey|聚水潭key' => 'require|max:50',
//            'jushuitanToken|聚水潭token' => 'require|max:50',
//            'jushuitanRefreshToken|聚水潭token续期时间' => 'require',
//            'jushuitanRefreshOrder|聚水潭同步订单时间' => 'require|integer|between:1,1440',
            'qq|客服群' => 'max:20000',
            'server|客服二维码' => 'require',
            'serverUrl|客服二维码连接' => 'require|length:0,255',
            'qr_note|客服二维码描述' => 'require|length:0,100',
            'work_time|客服上班时间' => 'require|length:0,100',

            'youSwitch|优云宝充值开关' => 'require|in:on,off',
            'youAppid|优云宝appid' => 'require|length:0,100',
            'youAppkey|优云宝appkey' => 'require|length:0,100',
            'youWechatSwitch|优云宝微信充值开关' => 'require|in:on,off',
            'youAlipaySwitch|优云宝支付宝充值开关' => 'require|in:on,off',
            'youQQSwitch|优云宝QQ充值开关' => 'require|in:on,off',
        ];

        $result = parent::validator(input(), $rule);

        if (!is_null($result)) parent::ajax_exception(0, $result);
    }

    //充值，删除配置文件
    public function reset()
    {
        $this->storage->unlink_files();
    }

    //默认数据
    private function defaults()
    {
        return [
            'webName' => '米礼网',
            'webTitle' => '米礼网',
            'webKeyword' => '米礼网',
            'webDesc' => '米礼网',
            'webSwitch' => 'on',
            'webCloseReason' => '网站维护中',
            'fwb-content' => '请谨慎下单',
            'logo' => config('young.image_not_found'),
            'webCopyright' => '版权',
            'userCommiss' => '100',
            'rechargeBase' => '100',
            'rechargeTimes' => '10',
            'rechargeSwitch' => 'on',
            'rechargeGradeSwitch' => 'on',
//            'rechargeMan' => '未配置',
//            'rechargeNo' => '未配置',
//            'rechargeBank' => '未配置',
//            'rechargeAddress' => '未配置',
//            'rechargeNote' => '未配置',
//            'rechargeAudit' => '未配置',
            'login' => config('young.image_not_found'),
            'loginUrl' => 'http://',
            'reg' => config('young.image_not_found'),
            'regUrl' => 'http://',
            'goods_number' => '每单至多购买{$number}件该商品',
            'self_default' => '请完善个人资料',
            'withdraw' => '将在24小时内处理您的提现申请',
            'loginReason' => '我们提供赠品采购、发货、一站式服务。',
//            'jushuitanShopid' => 10306045,
//            'jushuitanId' => 'c4bee67756d584195e367a8e44dc6f8c',
//            'jushuitanKey' => '0951cf9b1b392420f17d788cfd39f7c5',
//            'jushuitanToken' => '32e8833df97187b82b53f31584716876',
//            'jushuitanRefreshToken' => '05-01',
//            'jushuitanRefreshOrder' => '5',
            'qq' => '',
            'server' => config('young.image_not_found'),
            'serverUrl' => 'http://',
            'qr_note' => '未填写',
            'work_time' => '24小时上班',

            'youSwitch' => 'off',
            'youAppid' => '未填写',
            'youAppkey' => '未填写',
            'youWechatSwitch' => 'off',
            'youAlipaySwitch' => 'off',
            'youQQSwitch' => 'off',
        ];
    }

    //上传新的logo
    public function image(Request $request)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('images');

        $location = 'logo_' . time();

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($this->dir, $location);

        // 上传失败获取错误信息
        if (!$info) parent::ajax_exception(000, $file->getError());

        $location = '/' . $this->dir . '/' . $info->getSaveName();

        return [
            'image' => $location,
        ];
    }

    //删除未使用的logo
    public function image_delete($set)
    {
        if (!is_dir($this->dir)) return;//不是文件夹

        $files = scandir($this->dir);//读取文件

        //循环文件
        foreach ($files as $v) {

            if ($v == '.' || $v == '..') continue;//过滤

            $file = $this->dir . '/' . $v;//文件路径

            $a = (('/' . $file) != $set['logo']);
            $b = (('/' . $file) != $set['login']);
            $c = (('/' . $file) != $set['reg']);
            $d = (('/' . $file) != $set['server']);

            if ($a && $b && $c && $d) unlink($file);//删除未使用图片
        }
    }
}