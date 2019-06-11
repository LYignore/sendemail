<?php
namespace Lyignore\Sendemail;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Lyignore\Sendemail\Contracts\DatasInterface;
use Lyignore\Sendemail\Contracts\ExcelInterface;
use Lyignore\Sendemail\Contracts\SemdEmailInterface;
use Lyignore\Sendemail\Support\Config;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use SendGrid\Mail\Mail;

class SendEmail implements SemdEmailInterface {
    protected $config;

    protected $dataHandle;

    protected $excelHandle;

    protected $sendHandle;

    protected $datas=[];

    protected $tmpExcelPath;

    public function __construct(array $config)
    {
        $this->config = new Config($config);

        //配置数据库信息
        if(empty($config['db'])){
            throw new \Exception('Please fill in the database configuration information');
        }
        $this->configDatabase($config['db']);
    }

    protected function configDatabase($config)
    {
        $this->dataHandle = new Datas($config);
    }

    /**
     * 查询出对应的数据，用以生成Excel
     */
    public function getData(string $tmp_filename)
    {
        $dateTimeto   = date('Y-m-d H:i:s', time());
        $dateTimeFrom = $this->configSyncLog($tmp_filename, $dateTimeto);
        $sql = "SELECT * FROM `users` WHERE created_at>? AND created_at<=?";
        $datas = $this->dataHandle->select($sql, [$dateTimeFrom, $dateTimeto]);
        $this->datas = array_merge($this->datas, $datas);
        return $this->datas;
    }

    public function createExcel($excelname = 'demo')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $AZ = range('A', 'Z');
        if(!empty($this->datas)){
            foreach ($this->datas as $ke=>$content){
                $ks = 0;
                foreach ($content as $k => $cell){
                    $sheet->getColumnDimension($AZ[$ke])->setAutoSize(true);
                    $sheet->setCellValue($AZ[$ks].($ke), $cell);
                    $ks++;
                }
            }
        }
        $objWrite = IOFactory::createWriter($spreadsheet,'Xls');

        $temp_file = tempnam(sys_get_temp_dir(), $excelname);
        $objWrite->save($temp_file);
        $this->tmpExcelPath = $temp_file;
        return $temp_file;
//        $path = './uploads/';
//        if(!is_dir($path)){
//            mkdir($path);
//            chmod($path, 0777);
//        }
//        $excelnames = './uploads/'.$excelname.'.xlsx';
//        $objWrite->save($excelnames);
    }

    public function send()
    {
        $email = new Mail();
        $email->setFrom()
    }

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