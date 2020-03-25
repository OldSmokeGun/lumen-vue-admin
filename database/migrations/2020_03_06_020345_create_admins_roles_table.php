<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsRolesTable extends Migration
{
    private $tableName = 'admins_roles';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_id')->nullable(false)->default(0)->comment('用户 id');
            $table->integer('role_id')->nullable(false)->default(0)->comment('角色 id');
            $table->bigInteger('create_time')->nullable(false)->default(0);
            $table->bigInteger('update_time')->nullable(false)->default(0);
            $table->index('admin_id');
            $table->index('role_id');
            $table->index('create_time');
            $table->index('update_time');
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';
        });

        DB::statement("ALTER TABLE {$this->tableName} comment '管理员和角色关系表'");
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
