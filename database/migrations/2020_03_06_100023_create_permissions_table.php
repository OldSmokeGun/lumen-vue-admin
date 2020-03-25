<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    private $tableName = 'permissions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('identification', 255)->nullable(false)->default('')->comment('权限唯一标识');
            $table->string('title', 100)->nullable(false)->default('')->comment('权限名称（ 非路由类型设置无效，路由类型对应菜单名称 ）');
            $table->string('icon', 100)->nullable(false)->default('')->comment('权限图标（ 非路由类型设置无效，路由类型对应菜单图标 ）');
            $table->string('component', 100)->nullable(false)->default('')->comment('权限组件（ 如果权限标识是以 http: 或 https: 开头的完整地址或非路由类型，则此字段设置无效 ）');
            $table->string('redirect', 255)->nullable(false)->default('')->comment('重定向标识（ 如果权限标识是以 http: 或 https: 开头的完整地址或非路由类型，则此字段设置无效 ）');
            $table->string('description', 255)->nullable(false)->default('')->comment('权限描述');
            $table->tinyInteger('type')->nullable(false)->default(0)->comment('权限类型（ 0 表示路由，1 表示按钮或其他 ）');
            $table->integer('parent_id')->nullable(true)->default(0)->comment('上级权限 id（ 对于非路由类型只是展示作用 ）');
            $table->integer('sort')->nullable(false)->default(0)->comment('排序（ 仅对路由类型有效 ）');
            $table->integer('lft')->nullable(false)->default(0)->comment('左值');
            $table->integer('rgt')->nullable(false)->default(0)->comment('右值');
            $table->tinyInteger('status')->nullable(false)->default(1)->comment('是否可用（ 1 是 0 否 ）');
            $table->tinyInteger('display')->nullable(false)->default(1)->comment('是否显示（ 1 是 0 否 ）（ 仅对路由类型有效 ）');
            $table->bigInteger('create_time')->nullable(false)->default(0);
            $table->bigInteger('update_time')->nullable(false)->default(0);
            $table->unique('identification');
            $table->index('title');
            $table->index('component');
            $table->index('type');
            $table->index('parent_id');
            $table->index('sort');
            $table->index('lft');
            $table->index('rgt');
            $table->index('status');
            $table->index('display');
            $table->index('create_time');
            $table->index('update_time');
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';
        });

        DB::statement("ALTER TABLE {$this->tableName} comment '权限表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
