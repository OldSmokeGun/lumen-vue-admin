<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    private $tableName = 'admins';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 32)->nullable(false)->default('')->comment('用户名');
            $table->string('password', 255)->nullable(false)->default('')->comment('用户密码');
            $table->string('nickname', 255)->nullable(false)->default('')->comment('用户昵称');
            $table->string('avatar', 255)->nullable(false)->default('')->comment('用户头像');
            $table->string('email', 100)->nullable(false)->default('')->comment('email');
            $table->text('token')->nullable(false)->comment('用户 token');
            $table->tinyInteger('status')->nullable(false)->default(1)->comment('是否可用（ 1 是 0 否 ）');
            $table->integer('last_login_at')->nullable(false)->default(0)->comment('最后登陆时间');
            $table->string('last_login_ip', 36)->nullable(false)->default(0)->comment('最后登陆 IP');
            $table->integer('created_at')->nullable(false)->default(0);
            $table->integer('updated_at')->nullable(false)->default(0);
            $table->unique('username');
            $table->index('nickname');
            $table->index('email');
            $table->index('status');
            $table->index('last_login_at');
            $table->index('last_login_ip');
            $table->index('created_at');
            $table->index('updated_at');
            $table->charset = 'utf8mb4';
            $table->engine = 'InnoDB';
        });

        DB::statement("ALTER TABLE {$this->tableName} comment '管理员表'");
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
