<?php
namespace Lyignore\Sendemail\Contracts;

interface DatasInterface{
    // 连接数据库
    public function getConnection();

    // 数据库检索
    public function execute(string $sql, array $params);
}