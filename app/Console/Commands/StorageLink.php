<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;

class StorageLink extends Command
{
    protected $signature = 'storage:link';

    protected $description = 'create symlink from storage/app to public/storage';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $target = base_path('storage/app');
        $link   = base_path('public/storage');

        if (!windows_os()) {
            return symlink($target, $link);
        }

        $mode = is_dir($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

}
