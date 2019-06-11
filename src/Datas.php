<?php
namespace Lyignore\Sendemail;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Lyignore\Sendemail\Contracts\DatasInterface;

class Datas implements DatasInterface{
    protected $datas;

    protected $times;

    protected $lastsql;

    protected $config;

    protected $connection;

    protected $handle;

    public function __construct($config)
    {
        $this->config = array(
            'dbname' => $config['DB_DATABASE']??'mydb',
            'user'   => $config['DB_USERNAME']??'root',
            'password' => $config['DB_PASSWORD']??'',
            'host'   => $config['DB_HOST']??'localhost',
            'driver' => $config['DB_DRIVER']??'pdo_mysql',
            'port'   => $config['DB_PORT']??'3306',
        );
    }

    public function getConnection()
    {
        $configHandle = new Configuration();
        $this->connection = DriverManager::getConnection($this->config, $configHandle);
    }

    public function execute($sql, array $params)
    {
        if(!$this->connection instanceof  DriverManager){
            $this->getConnection();
        }
        $this->lastsql = $sql;
        try{
            $this->handle = $stmt = $this->connection->prepare($sql);
            if(!empty($params)){
                foreach ($params as $key => $val){
                    $stmt->bindValue($key+1, $val);
                }
            }
            //$this->handle = $stmt = $this->connection->query($sql);
            $stmt->execute();
            return $this->handle;
        }catch (\Exception $e){
            throw new \Exception('SQL running error: '. $this->lastsql);
        }
    }

    public function select($sql, $params=null){
        if(is_null($params)){
            $params = [];
        }
        $statement = $this->execute($sql, $params);
        return $statement->fetchAll();
    }
}