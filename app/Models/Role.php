<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $table      = 'roles';
    protected $dateFormat = 'U';

    public function getCreatedAtAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at']);
    }

    public function getUpdatedAtAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['updated_at']);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions', 'role_id', 'permission_id');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admins_roles', 'role_id', 'admin_id');
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
            'created_at',
            'updated_at'
        ])
            ->when($search['name'], function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($searchStatus, function ($query) use ($status) {
                $query->where(['status' => $status]);
            })
            ->idSort()
            ->paginate($search['limit']);

        foreach ($roles as $role) {
            $role->permissions = $role->permissions()->select(['permissions.id'])->get()->toArray();
        }

        return $roles->toArray();
    }

    public function createRole(array $data): bool
    {
        DB::beginTransaction();

        try {
            $this->name        = $data['name'];
            $this->description = $data['description'];
            $this->status      = $data['status'];

            if (!$this->save()) throw new \Exception();

            $this->permissions()->attach(array_fill_keys($data['permissions'], ['created_at' => time()]));

            DB::commit();
            return true;

        } catch (\Exception $exception) {

            DB::rollBack();
            return false;
        }
    }

    public function updateRole(array $data): bool
    {
        DB::beginTransaction();

        try {
            isset($data['name']) && $this->name = $data['name'];
            isset($data['description']) && $this->description = $data['description'];
            isset($data['status']) && $this->status = $data['status'];

            if (!$this->save()) throw new \Exception();

            isset($data['permissions']) && $this->permissions()->sync(array_fill_keys($data['permissions'], ['updated_at' => time()]));

            DB::commit();
            return true;

        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function deleteRole(): bool
    {
        DB::beginTransaction();

        try {
            $roleResult = $this->delete();

            if (!$roleResult) throw new \Exception();

            $this->permissions()->detach();
            $this->admins()->detach();

            DB::commit();
            return true;

        } catch (\Exception $exception) {

            DB::rollBack();
            return false;
        }
    }
}
