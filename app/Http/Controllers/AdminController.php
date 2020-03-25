<?php

namespace App\Http\Controllers;

use App\Facades\HttpResponse;
use App\Models\Admin as AdminModel;
use App\Utils\HttpResponse\HttpResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected function formValidator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'username' => 'required|max:8',
            'nickname' => 'required|max:12',
            'password' => 'required_without:id|max:16',
            'avatar'   => 'required|string',
            'email'    => 'required|email',
            'status'   => 'integer',
            'roles'    => 'array',
        ], [
            'username.required' => '用户名必须',
            'username.max'      => '用户名不能超过 8 个字符',
            'nickname.required' => '昵称必须',
            'nickname.max'      => '昵称不能超过 12 个字符',
            'password.required' => '密码必须',
            'password.max'      => '密码不能超过 16 个字符',
            'avatar.required'   => '请上传头像',
            'avatar.string'     => '头像地址类型错误',
            'email.required'    => '请填写邮箱',
            'email.email'       => '邮箱格式不正确',
            'status'            => '状态值类型错误',
            'roles.array'       => '角色值类型错误',
        ]);
    }

    /**
     * 管理员列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $search = [
            'username' => strval($request->input('username', '')),
            'nickname' => strval($request->input('nickname', '')),
            'email'    => strval($request->input('email', '')),
            'status'   => $request->input('status') ?? '',
            'page'     => intval($request->input('page', 1)),
            'limit'    => intval($request->input('limit', 10)),
        ];

        $model = new AdminModel();

        $list = $model->getAdminList($search);

        return HttpResponse::successResponse($list);
    }

    /**
     * 获取管理员全信息（包括权限）
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $token = strval($request->input('token', ''));

        if ( !$token )
        {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $fields = [
            'id',
            'username',
            'nickname',
            'avatar',
            'email',
            'last_login_time',
            'last_login_ip',
        ];

        $model = new AdminModel();

        $admin = $model->getAdminPermissionsByToken($token, $fields);

        if ( $admin )
        {
            $admin = [
                'id'              => $admin['id'],
                'username'        => $admin['username'],
                'nickname'        => $admin['nickname'],
                'avatar'          => $admin['avatar'],
                'email'           => $admin['email'],
                'last_login_date' => $admin['last_login_time'],
                'last_login_ip'   => $admin['last_login_ip'],
                'permission_maps' => $admin['permission_maps']
            ];
        }

        return HttpResponse::successResponse($admin);
    }

    /**
     * 创建管理员
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = $this->formValidator($request);

        if ( $validator->fails() )
        {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $admin = [
            'username' => strval($request->input('username')),
            'nickname' => strval($request->input('nickname')),
            'password' => strval($request->input('password')),
            'avatar'   => strval($request->input('avatar')),
            'email'    => strval($request->input('email')),
            'status'   => intval($request->input('status', 1)),
            'roles'    => $request->input('roles', []),
        ];

        if ( count($admin['roles']) > 3 )
        {
            return HttpResponse::failedResponse('最多只能选择三个角色');
        }

        $model = new AdminModel();

        $result = $model->createAdmin($admin);

        if ( !$result ) return HttpResponse::failedResponse('数据保存失败');

        return HttpResponse::successResponse();
    }

    /**
     * 更新管理员
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if ( !$request->input('id') )
        {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $validator = $this->formValidator($request);

        if ( $validator->fails() )
        {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $admin = [
            'id'       => strval($request->input('id')),
            'username' => strval($request->input('username')),
            'nickname' => strval($request->input('nickname')),
            'password' => strval($request->input('password')),
            'avatar'   => strval($request->input('avatar')),
            'email'    => strval($request->input('email')),
            'status'   => intval($request->input('status', 1)),
            'roles'    => $request->input('roles', []),
        ];

        if ( count($admin['roles']) > 3 )
        {
            return HttpResponse::failedResponse('最多只能选择三个角色');
        }

        $model = new AdminModel();

        $result = $model->updateAdmin($admin);

        if ( !$result ) return HttpResponse::failedResponse('数据更新失败');

        return HttpResponse::successResponse();
    }

    /**
     * 删除管理员
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function delete(Request $request)
    {
        if ( !$request->input('id') )
        {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        if ( (int) $request->input('id') === 1 )
        {
            return HttpResponse::failedResponse('不能删除超级管理员');
        }

        $result = (new AdminModel())->deleteAdmin($request->input('id'));

        if ( !$result ) return HttpResponse::failedResponse('删除失败');

        return HttpResponse::successResponse();
    }

    /**
     * 编辑管理员
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function edit(Request $request)
    {
        if ( !$request->input('id') )
        {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $params          = $request->input();
        $fieldsWhiteList = [
            'id'       => 0,
            'username' => '',
            'avatar'   => '',
            'email'    => '',
            'status'   => 1
        ];

        $fileds = array_intersect_key($params, $fieldsWhiteList);

        $model = new AdminModel();

        $result = $model->editAdmin($fileds);

        if ( !$result ) return HttpResponse::failedResponse('数据修改失败');

        return HttpResponse::successResponse();
    }

    /**
     * 管理员头像上传
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        if ( !$request->hasFile('avatar') )
        {
            return HttpResponse::failedResponse('上传出错');
        }

        if ( !$request->file('avatar')->isValid() )
        {
            return HttpResponse::failedResponse('上传的文件无效');
        }

        $savePath = 'public/upload/admin/avatar';

        $path = $request->avatar->store($savePath);

        if (!$path)
        {
            return HttpResponse::failedResponse('上传文件失败');
        }

        return HttpResponse::successResponse([
            'avatar_path' => Storage::url($path)
        ]);
    }

    /**
     * 获取表单角色列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function roles()
    {
        $roles = (new AdminModel())->getRoles();

        return HttpResponse::successResponse($roles);
    }
}
