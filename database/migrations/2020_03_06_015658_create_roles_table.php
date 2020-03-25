<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    private $tableName = 'roles';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->nullable(false)->default('')->comment('角色名称');
            $table->string('description', 255)->nullable(false)->default('')->comment('角色描述');
            $table->tinyInteger('status')->nullable(false)->default(1)->comment('是否可用（ 1 是 0 否 ）');
            $table->bigInteger('create_time')->nullable(false)->default(0);
            $table->bigInteger('update_time')->nullable(false)->default(0);
            $table->unique('name');
            $table->index('status');
            $table->index('create_time');
            $table->index('update_time');
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';
        });

        DB::statement("ALTER TABLE {$this->tableName} comment '角色表'");
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
