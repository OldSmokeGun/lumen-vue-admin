<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $table      = 'roles';
    protected $dateFormat = 'U';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }

    public function getUpdateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['update_time']);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions', 'role_id', 'permission_id');
    }

    /**
     * @param Builder $query
     * @param string $order
     *
     * @return mixed
     */
    public function scopeIdSort($query, $order = 'asc')
    {
        return $query->orderBy('id', $order);
    }

    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopeEnable($query)
    {
        return $query->where(['status' => 1]);
    }

    public function getRoleList(array $search): ?array
    {
        $status       = $search['status'];
        $searchStatus = $search['status'] === '' ? false : true;

        $roles = $this->select([
                'id',
                'name',
                'description',
                'status',
                'create_time',
                'update_time'
            ])
            ->when($search['name'], function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($searchStatus, function ($query) use ($status) {
                $query->where(['status' => $status]);
            })
            ->idSort()
            ->paginate($search['limit']);

        foreach ( $roles as $role )
        {
            $role->permissions = $role->permissions()->select(['permissions.id'])->get()->toArray();
        }

        return $roles->toArray();
    }

    public function createRole(array $role): bool
    {
        DB::beginTransaction();

        try
        {
            $this->name        = $role['name'];
            $this->description = $role['description'];
            $this->status      = $role['status'];

            if ( !$this->save() ) throw new \Exception();

            if ( $role['permissions'] )
            {
                $rolesPermissions = [];

                foreach ( $role['permissions'] as $role )
                {
                    $rolesPermissions[] = ['role_id' => $this->id, 'permission_id' => $role, 'create_time' => time()];
                }

                if ( !RolePermission::insert($rolesPermissions) ) throw new \Exception();
            }

            DB::commit();
            return true;

        } catch ( \Exception $exception ) {
            DB::rollBack();
            return false;
        }

    }

    public function updateRole(array $role): bool
    {
        $model = $this->find($role['id']);

        DB::beginTransaction();

        try
        {
            $model->name        = $role['name'];
            $model->description = $role['description'];
            $model->status      = $role['status'];

            if ( !$model->save() ) throw new \Exception();

            RolePermission::where(['role_id' => $role['id']])->delete();

            if ( $role['permissions'] )
            {
                $rolesPermissions = [];

                foreach ( $role['permissions'] as $permission )
                {
                    $rolesPermissions[] = ['role_id' => $role['id'], 'permission_id' => $permission, 'create_time' => time()];
                }

                if ( !RolePermission::insert($rolesPermissions) ) throw new \Exception();
            }

            DB::commit();
            return true;

        } catch ( \Exception $exception ) {
            DB::rollBack();
            return false;
        }

    }

    public function deleteRole(int $id): bool
    {
        DB::beginTransaction();

        try
        {
            AdminRole::where(['role_id' => $id])->delete();
            RolePermission::where(['role_id' => $id])->delete();
            $roleResult = $this->destroy($id);

            if ( !$roleResult ) throw new \Exception();

            DB::commit();
            return true;

        } catch ( \Exception $exception ) {
            DB::rollBack();
            return false;
        }
    }

    public function editRole(array $fields): bool
    {
        $model = $this->find($fields['id']);

        unset($fields['id']);

        foreach ( $fields as $fieldKey => $fieldValue )
        {
            $model->$fieldKey = $fieldValue;
        }

        return  $model->save();
    }

}
