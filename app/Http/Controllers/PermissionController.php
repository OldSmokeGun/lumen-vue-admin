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
            'identification' => 'sometimes|required|max:100',
            'title'          => 'sometimes|required|max:50',
            'icon'           => 'max:50',
            'component'      => 'max:50',
            'redirect'       => 'max:100',
            'description'    => 'max:100',
            'type'           => 'sometimes|required|integer',
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
            'status'         => $request->input('status') ?? '',
        ];

        $list = (new PermissionModel())->getPermissonList($search);

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
        if ($request->isMethod('GET')) {
            return HttpResponse::successResponse($this->formTrees());
        }

        $validator = $this->formValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $permission = [
            'identification' => strval($request->input('identification')),
            'title'          => strval($request->input('title')),
            'icon'           => strval($request->input('icon', '')),
            'redirect'       => strval($request->input('redirect', '')),
            'description'    => strval($request->input('description', '')),
            'type'           => intval($request->input('type', 0)),
            'parent_id'      => intval($request->input('parent_id', 0)),
            'sort'           => intval($request->input('sort', 0)),
            'status'         => intval($request->input('status', 1)),
            'display'        => intval($request->input('display', 1)),
        ];

        if ($permission['type']) {
            $permission['icon']     = '';
            $permission['redirect'] = '';
            $permission['display']  = 0;
        }

        if (preg_match('/^https?:\/\//', $permission['identification'])) {
            $permission['redirect'] = '';
        }

        $result = (new PermissionModel())->createPermission($permission);

        if (!$result) return HttpResponse::failedResponse('数据保存失败');

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
        if (!$request->input('id')) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $permission = PermissionModel::find($request->input('id'));
        if (!$permission) HttpResponse::failedResponse('权限不存在');

        if ($request->isMethod('GET')) {
            return HttpResponse::successResponse($this->formTrees());
        }

        $validator = $this->formValidator($request);

        if ($validator->fails()) {
            return HttpResponse::failedResponse($validator->errors()->first());
        }

        $data = [];
        $request->has('id') && $data['id'] = intval($request->input('id'));
        $request->has('identification') && $data['identification'] = strval($request->input('identification'));
        $request->has('title') && $data['title'] = strval($request->input('title'));
        $request->has('icon') && $data['icon'] = strval($request->input('icon', ''));
        $request->has('redirect') && $data['redirect'] = strval($request->input('redirect', ''));
        $request->has('description') && $data['description'] = strval($request->input('description', ''));
        $request->has('type') && $data['type'] = intval($request->input('type', 0));
        $request->has('parent_id') && $data['parent_id'] = intval($request->input('parent_id', 0));
        $request->has('sort') && $data['sort'] = intval($request->input('sort', 0));
        $request->has('status') && $data['status'] = intval($request->input('status', 1));
        $request->has('display') && $data['display'] = intval($request->input('display', 1));

        if (isset($data['parent_id'])) {
            if ($data['parent_id'] == $data['id']) {
                return HttpResponse::failedResponse('父节点不能为自身节点');
            }

            if ($data['parent_id']) {
                $parent = PermissionModel::find($data['parent_id']);
                $child  = PermissionModel::find($data['id']);

                if (($parent->lft > $child->lft) && ($parent->rgt < $child->rgt)) {
                    return HttpResponse::failedResponse('不能添加到后代节点');
                }
            }
        }

        if (isset($data['type']) && $data['type']) {
            $data['icon']      = '';
            $data['redirect']  = '';
            $data['display']   = 0;
        }

        if (isset($data['identification']) && preg_match('/^https?:\/\//', $data['identification'])) {
            $data['redirect']  = '';
        }

        $result = $permission->updatePermission($data);

        if (!$result) return HttpResponse::failedResponse('数据更新失败');

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
        if (!$request->input('id')) {
            return HttpResponse::failedResponse(HttpResponseCode::ILLEGAL_REQUEST_CODE_MESSAGE, HttpResponseCode::ILLEGAL_REQUEST_CODE);
        }

        $permission = PermissionModel::find($request->input('id'));
        if (!$permission) HttpResponse::failedResponse('权限不存在');

        $childrenCount = PermissionModel::where(['parent_id' => $permission->id])->count();

        if ($childrenCount) {
            return HttpResponse::failedResponse('当前节点还有子节点，无法删除');
        }

        $result = $permission->deletePermission();

        if (!$result) return HttpResponse::failedResponse('删除失败');

        return HttpResponse::successResponse();
    }

    /**
     * 获取权限树
     *
     * @return array
     */
    protected function formTrees()
    {
        $model = new PermissionModel();
        $trees = $model->getPermissionTrees();

        array_unshift($trees, ['id' => 0, 'parent_id' => null, 'tree_title' => '顶级权限']);

        return $trees;
    }
}
