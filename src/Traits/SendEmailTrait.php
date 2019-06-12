<?php
namespace Lyignore\Sendemail\Traits;

trait SendEmailTrait{
    /**
     * 设置同步日期记录,暂时不引入redis,用文件记录上次的查询时间，代替redis
     * params filename_path
     * return datetime
     */
    protected function configSyncLog($path, $str = null)
    {
        if(!file_exists($path)){
            touch($path);
            chmod($path, 0777);
            if(!is_null($str)){
                file_put_contents($path, $str);
            }
            return date('Y-m-d H:i:s', time());
        }
        if(!is_readable($path) || !is_writable($path)){
            chmod($path, 0777);
        }
        $result = file_get_contents($path);
        if(!is_null($str)){
            file_put_contents($path, $str);
        }
        return $result;
    }
}