<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Inspiring;
use \Redis;

use log;

class DeleteUnusedfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteUnusedfiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除没使用但上传图片';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $Storage_files = [];
        $redis_files = [];


        $files = Storage::allFiles();   //遍历存储文件
        if (!$files){
            return msg(5,"文件仓库为空".__LINE__);
        }
        foreach ($files as $file){           //遍历结果去掉前缀
            $test = stripos($file,"jpg");
            if ($test){
                $Storage_replace = str_replace("public/image/","",$file);
                $Storage_files[] = $Storage_replace;
            }
        }


        try{
            $redis = new Redis();

            $redis->connect('image_redis_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
//        $redis->hSet('food_image',"test2", time()); // 存储图片上传时间 外部辅助脚本过期后删除

        $files = $redis->hkeys("food_image");
        foreach ($files as $file){           //遍历结果去掉前缀
            $redis_replace = str_replace("https://test.upick.com/storage/image/","",$file);
            $redis_files[] = $redis_replace;
        }


        //删除文件
        $intersection = array_diff($Storage_files,$redis_files); //找出存储但未使用的文件
        $disk = Storage::disk('img');
        foreach ($intersection as $file){   //遍历删除
            $disk->delete($file);
        }

    }
}
