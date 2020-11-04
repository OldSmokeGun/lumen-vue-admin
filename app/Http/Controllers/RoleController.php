<?php

namespace App\Http\Controllers;

use App\Facades\HttpResponse;
use App\Models\Permission as PermissionModel;
use App\Models\Role as RoleModel;
use App\Utils\HttpResponse\HttpResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    protected function formValidator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name'        => 'sometimes|required|max:16',
            'description' => 'sometimes|required|max:50',
            'status'      => 'integer',
        ], [
            'name.required'        => '角色名必须',
            'name.max'             => '角色名不能超过 16 个字符',
            'description.required' => '角色描述必须',
            'description.max'      => '角色描述不能超过 50 个字符',
            'status'               => '状态值类型错误'
        ]);
    }

    /**
     * 角色列表
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function list(Request $request)
    {
        $search = [
            'name'   => strval($request->input('name', '')),
            'page'   => intval($request->input('page', 1)),
            'status' => $request->input('status') ?? '',
            'limit'  => intval($request->input('limit', 10)),
        ];

        $list = (new RoleModel())->getRoleList($search);

        return HttpResponse::successResponse($list);
    }

    /**
     * 创建角色
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        if ($request->isMethod("GET")) {
            return HttpResponse::successResponse($this->formPermissionTrees());
        }

        $validator = $this->formValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $role = [
            'name'        => strval($request->input('name')),
            'description' => strval($request->input('description')),
            'status'      => intval($request->input('status', 1)),
            'permissions' => $request->input('permissions', []),
        ];

        $result = (new RoleModel())->createRole($role);

        if (!$result) return HttpResponse::failedResponse('数据保存失败');

        return HttpResponse::successResponse();
    }

    /**
     * 更新角色
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

        $role = RoleModel::find($request->input('id'));
        if (!$role) HttpResponse::failedResponse('角色不存在');

        if ($request->isMethod("GET")) {
            return HttpResponse::successResponse($this->formPermissionTrees());
        }

        $validator = $this->formValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $data = [];
        $request->has('id') && $data['id'] = intval($request->input('id'));
        $request->has('name') && $data['name'] = strval($request->input('name'));
        $request->has('description') && $data['description'] = strval($request->input('description'));
        $request->has('status') && $data['status'] = intval($request->input('status'));
        $request->has('permissions') && $data['permissions'] = $request->input('permissions', []);

        $result = $role->updateRole($data);

        if (!$result) return HttpResponse::failedResponse('数据更新失败');

        return HttpResponse::successResponse();
    }

    /**
     * 删除角色
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

        $role = RoleModel::find($request->input('id'));
        if (!$role) HttpResponse::failedResponse('角色不存在');

        $result = $role->deleteRole();

        if (!$result) return HttpResponse::failedResponse('删除失败');

        return HttpResponse::successResponse();
    }

    protected function formPermissionTrees()
    {
        $model = new PermissionModel();

        return $model->getPermissionTrees();
    }
}
