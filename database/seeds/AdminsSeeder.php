<?php

use \Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\Hash;

class AdminsSeeder extends Seeder
{
    public function run()
    {
        $admins = [
            ['username' => 'root', 'password' => Hash::make('root'), 'nickname' => '超级管理员', 'token' => '', 'create_time' => time()],
            ['username' => 'admin', 'password' => Hash::make('admin'), 'nickname' => '管理员', 'token' => '', 'create_time' => time()],
        ];

        DB::table('admins')
            ->insert($admins);
    }
}
