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
            'name'        => 'required|max:16',
            'description' => 'required|max:50',
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

        $model = new RoleModel();

        $list = $model->getRoleList($search);

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
        $validator = $this->formValidator($request);

        if ( $validator->fails() )
        {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $role = [
            'name'        => strval($request->input('name')),
            'description' => strval($request->input('description')),
            'status'      => intval($request->input('status', 1)),
            'permissions' => $request->input('permissions', []),
        ];

        $model = new RoleModel();

        $result = $model->createRole($role);

        if ( !$result ) return HttpResponse::failedResponse('数据保存失败');

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
        if ( !$request->input('id') )
        {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $validator = $this->formValidator($request);

        if ( $validator->fails() )
        {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $role = [
            'id'          => strval($request->input('id')),
            'name'        => strval($request->input('name')),
            'description' => strval($request->input('description')),
            'status'      => intval($request->input('status', 1)),
            'permissions' => $request->input('permissions', []),
        ];

        $model = new RoleModel();

        $result = $model->updateRole($role);

        if ( !$result ) return HttpResponse::failedResponse('数据更新失败');

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
        if ( !$request->input('id') )
        {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $result = (new RoleModel())->deleteRole($request->input('id'));

        if ( !$result ) return HttpResponse::failedResponse('删除失败');

        return HttpResponse::successResponse();
    }

    /**
     * 编辑角色
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
            'id'          => 0,
            'name'        => '',
            'description' => '',
            'status'      => 1
        ];

        $fileds = array_intersect_key($params, $fieldsWhiteList);

        $model = new RoleModel();

        $result = $model->editRole($fileds);

        if ( !$result ) return HttpResponse::failedResponse('数据修改失败');

        return HttpResponse::successResponse();
    }

    public function permissions()
    {
        $model = new PermissionModel();

        $data = [
            'routes' => $model->getPermissionTrees(0),
            'others' => $model->getPermissionTrees(1)
        ];

        return HttpResponse::successResponse($data);
    }
}
