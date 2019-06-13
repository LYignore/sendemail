<?php
namespace Lyignore\Sendemail\Contracts;

interface SemdEmailInterface{
    // 获取数据
    public function getData(string $sql, array $params);

    // 生成Excel文件
    public function createExcel();

    // 发送邮件
    public function send(array $emails);
}