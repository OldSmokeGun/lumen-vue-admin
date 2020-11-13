<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\NodeTrait;

class Permission extends Model
{
    use NodeTrait;

    protected $table      = 'permissions';
    protected $dateFormat = 'U';

    public function getLftName()
    {
        return 'lft';
    }

    public function getRgtName()
    {
        return 'rgt';
    }

    public function getParentIdName()
    {
        return 'parent_id';
    }

    public function getCreatedAtAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at']);
    }

    public function getUpdatedAtAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['updated_at']);
    }

    /**
     * @param Builder $query
     * @param string $order
     *
     * @return mixed
     */
    public function scopeSort($query)
    {
        return $query->orderBy('sort')->orderBy('identification');
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_permissions', 'permission_id', 'role_id');
    }

    public function getPermissonList(array $search): ?array
    {
        $status       = $search['status'];
        $searchStatus = $search['status'] === '' ? false : true;

        $permissions = $this
            ->when($search['identification'], function ($query, $identification) {
                $query->where('identification', 'like', "%{$identification}%");
            })
            ->when($search['title'], function ($query, $title) {
                $query->where('title', 'like', "%{$title}%");
            })
            ->when($searchStatus, function ($query) use ($status) {
                $query->where(['status' => $status]);
            })
            ->sort()
            ->get()
            ->toTree()
            ->toArray();

        return $permissions;
    }

    public function createPermission(array $data): bool
    {
        DB::beginTransaction();

        try {
            $this->identification = $data['identification'];
            $this->title          = $data['title'];
            $this->icon           = $data['icon'];
            $this->redirect       = $data['redirect'];
            $this->description    = $data['description'];
            $this->type           = $data['type'];
            $this->sort           = $data['sort'];
            $this->status         = $data['status'];
            $this->display        = $data['display'];

            if ($data['parent_id']) {
                $parent = $this->find($data['parent_id']);

                if (!$parent->appendNode($this)) throw new \Exception();

            } else {
                if (!$this->saveAsRoot()) throw new \Exception();
            }

            DB::commit();
            return true;

        } catch (\Exception $exception) {

            DB::rollBack();
            return false;
        }
    }

    public function updatePermission(array $data): bool
    {
        isset($data['identification']) && $this->identification = $data['identification'];
        isset($data['title']) && $this->title = $data['title'];
        isset($data['icon']) && $this->icon = $data['icon'];
        isset($data['redirect']) && $this->redirect = $data['redirect'];
        isset($data['description']) && $this->description = $data['description'];
        isset($data['type']) && $this->type = $data['type'];
        isset($data['parent_id']) && $this->parent_id = $data['parent_id'];
        isset($data['sort']) && $this->sort = $data['sort'];
        isset($data['status']) && $this->status = $data['status'];
        isset($data['display']) && $this->display = $data['display'];

        if (!$this->save()) return false;

        return true;
    }

    public function deletePermission(): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->delete();

            if (!$result) throw new \Exception();

            $this->roles()->detach();

            DB::commit();
            return true;

        } catch (\Exception $exception) {

            DB::rollBack();
            return false;
        }
    }

    public function getPermissionTrees(): array
    {
        return $this
            ->select([
                'id',
                'identification',
                'title as tree_title',
                'description',
                'parent_id',
                'lft',
                'rgt'
            ])
            ->sort()
            ->get()
            ->toTree()
            ->toArray();
    }
}
