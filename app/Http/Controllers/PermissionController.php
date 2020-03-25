<?php

namespace App\Http\Controllers;

use App\Facades\HttpResponse;
use App\Models\Permission as PermissionModel;
use App\Utils\HttpResponse\HttpResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    protected function formValidator(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'identification' => 'required|max:100',
            'title'          => 'required|max:50',
            'icon'           => 'max:50',
            'component'      => 'max:50',
            'redirect'       => 'max:100',
            'description'    => 'max:100',
            'type'           => 'required|integer',
            'parent_id'      => 'integer|nullable',
            'status'         => 'integer',
        ], [
            'identification.required' => '权限唯一标识必须',
            'identification.max'      => '权限唯一标识不能超过 100 个字符',
            'title.required'          => '权限标题必须',
            'title.max'               => '权限标题不能超过 50 个字符',
            'icon.max'                => '权限图标不能超过 50 个字符',
            'component.max'           => '权限组件不能超过 50 个字符',
            'redirect.max'            => '重定向标识不能超过 100 个字符',
            'description.max'         => '权限描述不能超过 100 个字符',
            'type.required'           => '类型值必须',
            'type.integer'            => '类型值类型错误',
            'parent_id.integer'       => '上级权限类型错误',
            'status.integer'          => '状态值类型错误'
        ]);
    }

    /**
     * 权限列表
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function list(Request $request)
    {
        $search = [
            'identification' => strval($request->input('identification', '')),
            'title'          => strval($request->input('title', '')),
            'type'           => $request->input('type') ?? '',
            'status'         => $request->input('status') ?? '',
        ];

        $model = new PermissionModel();

        $list = $model->getPermissonList($search);

        return HttpResponse::successResponse($list);
    }

    /**
     * 创建权限
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

        $permission = [
            'identification' => strval($request->input('identification')),
            'title'          => strval($request->input('title')),
            'icon'           => strval($request->input('icon', '')),
            'component'      => strval($request->input('component', '')),
            'redirect'       => strval($request->input('redirect', '')),
            'description'    => strval($request->input('description', '')),
            'type'           => intval($request->input('type', 0)),
            'parent_id'      => intval($request->input('parent_id', 0)),
            'sort'           => intval($request->input('sort', 0)),
            'status'         => intval($request->input('status', 1)),
            'display'        => intval($request->input('display', 1)),
        ];

        if ( $permission['type'] )
        {
            $permission['icon']      = '';
            $permission['component'] = '';
            $permission['redirect']  = '';
            $permission['display']   = 0;
        }

        if ( preg_match('/^https?:\/\//', $permission['identification']) )
        {
            $permission['component'] = '';
            $permission['redirect']  = '';
        }

        $model = new PermissionModel();

        $result = $model->createPermission($permission);

        if ( !$result ) return HttpResponse::failedResponse('数据保存失败');

        return HttpResponse::successResponse();
    }

    /**
     * 更新权限
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

        $permission = [
            'id'             => strval($request->input('id')),
            'identification' => strval($request->input('identification')),
            'title'          => strval($request->input('title')),
            'icon'           => strval($request->input('icon', '')),
            'component'      => strval($request->input('component', '')),
            'redirect'       => strval($request->input('redirect', '')),
            'description'    => strval($request->input('description', '')),
            'type'           => intval($request->input('type', 0)),
            'parent_id'      => intval($request->input('parent_id', 0)),
            'sort'           => intval($request->input('sort', 0)),
            'status'         => intval($request->input('status', 1)),
            'display'        => intval($request->input('display', 1)),
        ];

        if ( $permission['id'] == $permission['parent_id'] )
        {
            return HttpResponse::failedResponse('父节点不能为自身节点');
        }

        $model = new PermissionModel();

        if ( $permission['parent_id'] )
        {
            $parent = $model->find($permission['parent_id']);
            $child = $model->find($permission['id']);

            if ( ( $parent->lft > $child->lft ) && ( $parent->rgt < $child->rgt ) )
            {
                return HttpResponse::failedResponse('不能添加到后代节点');
            }
        }

        if ( $permission['type'] )
        {
            $permission['icon']      = '';
            $permission['component'] = '';
            $permission['redirect']  = '';
            $permission['display']   = 0;
        }

        if ( preg_match('/^https?:\/\//', $permission['identification']) )
        {
            $permission['component'] = '';
            $permission['redirect'] = '';
        }

        $result = $model->updatePermission($permission);

        if ( !$result ) return HttpResponse::failedResponse('数据更新失败');

        return HttpResponse::successResponse();
    }

    /**
     * 删除权限
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

        $model = new PermissionModel();

        $childrenCount = $model->where(['parent_id' => $request->input('id')])->count();

        if ( $childrenCount )
        {
            return HttpResponse::failedResponse('当前节点还有子节点，无法删除');
        }

        $result = (new PermissionModel())->deletePermission($request->input('id'));

        if ( !$result ) return HttpResponse::failedResponse('删除失败');

        return HttpResponse::successResponse();
    }

    /**
     * 编辑权限
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

        $permissionModel                = new PermissionModel();
        $enableChildrenPermissionsCount = $permissionModel->where([
            'parent_id' => $request->input('id'),
            'status' => 1,
        ])->count();

        if ( $enableChildrenPermissionsCount )
        {
            return HttpResponse::failedResponse('此节点还有可用的子节点');
        }

        $params          = $request->input();
        $fieldsWhiteList = [
            'id'             => 0,
            'identification' => '',
            'title'          => '',
            'icon'           => '',
            'component'      => '',
            'redirect'       => '',
            'description'    => '',
            'type'           => '',
            'sort'           => '',
            'status'         => 1
        ];

        $fileds = array_intersect_key($params, $fieldsWhiteList);

        $model = new PermissionModel();

        $result = $model->editPermission($fileds);

        if ( !$result ) return HttpResponse::failedResponse('数据修改失败');

        return HttpResponse::successResponse();
    }

    /**
     * 获取权限树
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function trees(Request $request)
    {
        $model = new PermissionModel();
        $type  = intval($request->input('type'));

        $trees = $model->getPermissionTrees($type);

        array_unshift($trees, ['id' => 0, 'parent_id' => null, 'tree_title' => '顶级权限']);

        return HttpResponse::successResponse($trees);
    }
}
