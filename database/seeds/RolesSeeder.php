<?php

use \Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => '管理员', 'description' => '管理员', 'created_at' => time()],
        ];

        DB::table('roles')
            ->insert($roles);
    }
}
