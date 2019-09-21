<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\UserAuth;
use function EasyWeChat\Kernel\Support\generate_sign;
use Illuminate\Http\Request;

class WechatController extends FrontendController
{
    /**
     * 数据解密
     * @param Request $request
     * @return array
     */
    private function decrypt(Request $request) {
        $code = $request->get('code');
        $encryptedData = $request->get('encryptedData');
        $iv = $request->get('iv');
        if(empty($code) || empty($encryptedData) || empty($iv)) return $this->error(401, "请求参数错误");
        $app = app('wechat.mini_program');
        $rss = $app->auth->session($code);
        if(empty($rss['session_key'])) return $this->error(402, "授权代码已过期");
        $data = $app->encryptor->decryptData($rss['session_key'], $iv, $encryptedData);
        return $this->success($data);
    }

    /**
     * 用户登陆
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $json = $this->decrypt($request);
        if($json['code']!=200) return $json;
        else $ret['info'] = $info = $json['data'];
        if(empty($info['unionId'])) return $this->error(201, "获取授权失败");
        $auth = UserAuth::where(['type' => 'WECHAT', 'identifier' => $info['unionId']])->first();
        if($auth) {
            $ret['info']['password'] = $auth->certificate;
            $ret['user'] = $auth->user->logged();
            $teams = $auth->user->teams; // 所在商户
            if(!empty($teams[0])) {
                $teams[0]->shops;  // 拥有店铺
                $ret['team'] = $teams[0];
            }
        }
        return $this->success($ret);
    }

    /**
     * 微信注册
     * @param Request $request
     * @return array
     */
    public function register(Request $request)
    {
        $json = $this->decrypt($request);
        if($json['code']!=200) return $json;
        $info = $request->get('info');
        $unionid = $info['unionId'];
        $password = $info['password'] = rand(1000, 9999); // 安全码
        $phone = phoneNumber('+' . $json['data']['countryCode'] . $json['data']['purePhoneNumber']);
        if ($phone == null) return $this->error(parent::ERROR_PHONE);
        $auth = UserAuth::where(['type' => 'WECHAT', 'identifier' => $unionid])
            // ->orWhere(function ($query) use ($phone) { $query->where(['type' => 'MOBILE', 'identifier' => $phone]); })
            ->first();
        if ($auth) $user = $auth->user;
        else {
            $user = User::where('unionid', $unionid)->orWhere('phone', $phone)->first();
            if (!$user) $user = new User;
            $user->phone = $phone;
            $user->unionid = $unionid;
            $user->name = $info['nickName'];
            $user->gender = $info['gender'];
            $user->avatar = $info['avatarUrl'];
            $user->save();
        }
        $user->auths()->firstOrCreate(['type' => 'MOBILE'], ['identifier' => $phone]);
        $user->auths()->updateOrCreate(['type' => 'WECHAT'], ['identifier' => $unionid, 'certificate' => $password]);
        $ret['user'] = $user->logged();
        $ret['info'] = $info;
        return $this->success($ret);
    }

    /**
     * 申请支付
     * @param Request $request
     * @return array
     */
    public function pay(Request $request) {
        $get = $request->all();
        $set = $request->get('set', 'default');   // 支持多个小程序配置
        $key = config('wechat.payment.'. $set .'.key');
        $app = \EasyWeChat::payment($set);
        $ret = $app->order->unify([
            'body'         => $get['title'],
            'out_trade_no' => $get['sn'],
            'trade_type'   => 'JSAPI',  // 必须为JSAPI
            'openid'       => $get['openid'], // 这里的openid为付款人的openid
            'total_fee'    => $get['amount'], // 总价
        ]);
        if(!empty($ret['result_code']) && $ret['result_code'] === 'SUCCESS') {
            $ret = [
                'appId'     => $ret['appid'],
                'timeStamp' => (String) time(),
                'nonceStr'  => $ret['nonce_str'],
                'package'   => 'prepay_id=' . $ret['prepay_id'],
                'signType'  => 'MD5',
            ];
            $ret['paySign'] = generate_sign($ret, $key);
        }
        return $ret;
    }

    /**
     * 微信授权
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function token(Request $request)
    {
        $app = \EasyWeChat::officialAccount();
        $gets = $request->all();
        if (!empty($gets['referer'])) $request->session()->put('referer', $gets['referer']); // 来源网址
        if (empty($gets['code'])) return $app->oauth->redirect(); // 前往获取授权
        $referer = $request->session()->get('referer', '/login');
        $info = $app->oauth->user()->getOriginal();
        $password = rand(1000, 9999); // 安全码
        UserAuth::where(['type' => 'WECHAT', 'identifier' => $info['unionid']])->update(['certificate' => $password]);
        $connect = strpos($referer, '?') === false ? '?' : '&';
        $url = $referer . $connect . 'authed=1&password=' . $password . '&' . http_build_query($info);
        return redirect($url);
    }

    /**
     * 二维码入口
     * @param $scene
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function code($scene)
    {
        $response = app('wechat.mini_program')->app_code->getUnlimit($scene, [ 'width'=>240, 'is_hyaline'=>true ]);
        $content = $response->getBody()->getContents();
        return response($content, 200, ['Content-Type' => 'image/png']);
    }

    public function jssdk(Request $request) {
        $app = \EasyWeChat::officialAccount();
        $gets = $request->all();
        if(!empty($gets['url'])) $app->jssdk->setUrl($gets['url']);
        // $str = $app->jssdk->buildConfig(['updateAppMessageShareData', 'updateTimelineShareData']);
        $str = $app->jssdk->buildConfig(['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ']);
        return $this->success(json_decode($str));
    }

}
