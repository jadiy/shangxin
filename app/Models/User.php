<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;
    use Notifiable, HasApiTokens;

    protected $fillable = ['username', 'password', 'phone', 'email', 'name', 'firstname', 'lastname', 'gender', 'avatar'];
    protected $hidden = ['password', 'remember_token', 'count'];
    protected $dates = ['login_at'];
    protected $appends = ['image_avatar'];
    public $path = 'global/avatars';

    public function fromDateTime($value)
    {
        return $this->asDateTime($value);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_users', 'userid', 'teamid')->orderBy('login_at', 'desc')->withPivot('owner', 'name', 'login_at', 'created_at');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'userid');
    }

    public function auths()
    {
        return $this->hasMany(UserAuth::class, 'user_id');
    }

    public function count()
    {
        return $this->hasOne(UserCount::class, 'user_id');
    }

    /**
     * 用户登陆
     * @return $this
     */
    public function logged()
    {
        // 登陆统计
        if (!$this->count) {
            $this->count = new UserCount;
            $this->count->user_id = $this->id;
        }
        $this->count->login_at = Carbon::now();
        $this->count->logins += 1;
        $this->count->save();
        return $this;
    }

    /**
     * 授权指定认证字段
     * @param $identifier
     * @return mixed
     */
    public function findForPassport($username)
    {
        $split = explode(':', $username);
        if (!empty($split[1])) {  // 兼容第三方认证
            $identifier = $split[1];
            $auth = UserAuth::Where('identifier', $identifier)->first();
            return $auth ? $auth->user : null;
        }
        return $this->where('username', $username)->orWhere('phone', $username)->orWhere('email', $username)->first();
    }

    /**
     * 授权密码验证方式
     * @param $password
     * @return bool
     */
    public function validateForPassportPasswordGrant($password)
    {
        $split = explode(':', $password);
        if (!empty($split[1])) {
            $type = strtoupper($split[0]);
            $password = $split[1];
            if ($type == 'PHONE') return Captcha::validate($this->phone, $password);
            else if ($type == 'EMAIL') return Captcha::validate($this->email, $password);
            $auth = $this->auths()->where('type', $type)->first();
            return $auth && $auth->certificate == $password;
        }
        $check = Hash::check($password, $this->password);
        // 兼容md5密码登录
        if (!$check) {
            if ($this->password == md5($password)) {
                $this->password = HASH::make($password);
                $this->save();
                return true;
            }
        }
        return $check;
    }

    /**
     * 获取密码授权
     * @param $username
     * @param $password
     * @return bool|mixed
     */
    public function getPasswordGrant($username = '', $password = '')
    {
        try {
            $client = new Client(['base_uri' => config('app.url')]);
            $data = ['form_params' => [
                'grant_type' => 'password',
                'client_id' => 2,
                'client_secret' => 'AxHexS1fLjXnUC2q8YKvK9L81ZmxuJXowAO0Vr3B',
                'username' => $username,
                'password' => $password,
            ]];
            $response = $client->request('post', '/api/oauth/token', $data);
            $oauth = json_decode((string) $response->getBody(), true);
            if ($oauth) {
                $this->access_token = $oauth['access_token'];
                // $this->refresh_token = $oauth['refresh_token'];
                // $this->expires_in = $oauth['expires_in'];
            }
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 增加用户头像字段 Avatar
     * @return string
     */
    public function getImageAvatarAttribute()
    {
        $default = '/images/avatar' . ($this->gender ? '.' . $this->gender : '') . '.png';
        return image_path($this->avatar, $this->path, $default);
    }

}
