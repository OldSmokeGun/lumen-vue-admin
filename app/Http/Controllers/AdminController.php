<?php

namespace App\Http\Controllers;

use App\Facades\HttpResponse;
use App\Models\Admin as AdminModel;
use App\Utils\HttpResponse\HttpResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected function formValidator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'username' => 'sometimes|required|max:8',
            'nickname' => 'sometimes|required|max:12',
            'password' => 'required_without:id|max:16',
            'avatar'   => 'sometimes|required|string',
            'email'    => 'sometimes|required|email',
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

    protected function resetPasswordFormValidator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'old_password' => 'required|max:16',
            'new_password' => 'required|max:16',
        ], [
            'old_password.required' => '原密码必须',
            'old_password.max'      => '原密码不能超过 16 个字符',
            'new_password.required' => '新密码必须',
            'new_password.max'      => '新密码不能超过 16 个字符',
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

        $list = (new AdminModel())->getAdminList($search);

        return HttpResponse::successResponse($list);
    }

    /**
     * 获取管理员全信息（包括权限）
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        $token = strval($request->input('token', ''));

        if (!$token) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $admin = (new AdminModel())->getAdminPermissionsByToken($token);

        if ($admin) {
            $admin = [
                'id'              => $admin['id'],
                'username'        => $admin['username'],
                'nickname'        => $admin['nickname'],
                'avatar'          => $admin['avatar'],
                'email'           => $admin['email'],
                'last_login_date' => $admin['last_login_at'],
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
        if ($request->isMethod("GET")) {
            return HttpResponse::successResponse($this->formRoles());
        }

        $validator = $this->formValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $data = [
            'username' => strval($request->input('username')),
            'nickname' => strval($request->input('nickname')),
            'password' => strval($request->input('password')),
            'avatar'   => strval($request->input('avatar')),
            'email'    => strval($request->input('email')),
            'status'   => intval($request->input('status', 1)),
            'roles'    => $request->input('roles', []),
        ];

        if (count($data['roles']) > 3) {
            return HttpResponse::failedResponse('最多只能选择三个角色');
        }

        $result = (new AdminModel())->createAdmin($data);

        if (!$result) return HttpResponse::failedResponse('数据保存失败');

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
        if (!$request->input('id')) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $admin = AdminModel::find($request->input('id'));
        if (!$admin) HttpResponse::failedResponse('用户不存在');

        if ($request->isMethod("GET")) {
            return HttpResponse::successResponse($this->formRoles());
        }

        $validator = $this->formValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $data = [];
        $request->has('id') && $data['id'] = intval($request->input('id'));
        $request->has('username') && $data['username'] = strval($request->input('username'));
        $request->has('nickname') && $data['nickname'] = strval($request->input('nickname'));
        $request->has('avatar') && $data['avatar'] = strval($request->input('avatar'));
        $request->has('email') && $data['email'] = strval($request->input('email'));
        $request->has('status') && $data['status'] = strval($request->input('status'));
        $request->has('roles') && $data['roles'] = $request->input('roles');

        if (isset($data['roles']) && count($data['roles']) > 3) {
            return HttpResponse::failedResponse('最多只能选择三个角色');
        }

        if (isset($data['status']) && (int)$request->input('id') === 1 && (int)$data['status'] === 0) {
            return HttpResponse::failedResponse('不能修改超级管理员状态');
        }

        $result = $admin->updateAdmin($data);

        if (!$result) return HttpResponse::failedResponse('数据更新失败');

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
        if (!$request->input('id')) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $admin = AdminModel::find($request->input('id'));
        if (!$admin) HttpResponse::failedResponse('用户不存在');

        if ((int)$admin->id === 1) {
            return HttpResponse::failedResponse('不能删除超级管理员');
        }

        $result = $admin->deleteAdmin();

        if (!$result) return HttpResponse::failedResponse('删除失败');

        return HttpResponse::successResponse();
    }

    public function resetPassword(Request $request)
    {
        if (!$request->input('id', 0)) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $admin = AdminModel::find($request->input('id'));
        if (!$admin) {
            return HttpResponse::failedResponse('用户不存在');
        }

        $result = $admin->resetPassword();

        if (!$result) return HttpResponse::failedResponse('重置密码失败');

        $admin->removeToken();

        return HttpResponse::successResponse();
    }

    public function updatePassword(Request $request)
    {
        $token = strval($request->input('token', ''));

        if (!$token) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $admin = (new AdminModel())->findByToken($token);
        if (!$admin) {
            return HttpResponse::failedResponse(HttpResponseCode::LOGIN_INVALID_CODE_MESSAGE, HttpResponseCode::LOGIN_INVALID_CODE);
        }

        $validator = $this->resetPasswordFormValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $oldPassword = strval($request->input('old_password'));
        $newPassword = strval($request->input('new_password'));

        if (!Hash::check($oldPassword, $admin->password)) {
            return HttpResponse::failedResponse('原密码错误');
        }

        $result = $admin->updatePassword($newPassword);

        if (!$result) return HttpResponse::failedResponse('修改密码失败');

        $admin->removeToken();

        return HttpResponse::successResponse();
    }

    /**
     * 获取表单角色列表
     *
     * @return array|null
     */
    public function formRoles()
    {
        return (new AdminModel())->getRoles();
    }
}
