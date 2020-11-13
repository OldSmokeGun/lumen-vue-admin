<?php

use \Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\Hash;

class AdminsSeeder extends Seeder
{
    public function run()
    {
        $admins = [
            ['username' => 'root', 'password' => Hash::make('root'), 'nickname' => '超级管理员', 'token' => '', 'created_at' => time()],
            ['username' => 'admin', 'password' => Hash::make('admin'), 'nickname' => '管理员', 'token' => '', 'created_at' => time()],
        ];

        DB::table('admins')
            ->insert($admins);
    }
}
