<?php
namespace Lyignore\Sendemail;

use Lyignore\Sendemail\Contracts\SemdEmailInterface;
use Lyignore\Sendemail\Support\Config;
use Lyignore\Sendemail\Traits\SendEmailTrait;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SendEmail implements SemdEmailInterface {
    use SendEmailTrait;
    protected $config;

    protected $dataHandle;

    protected $excelHandle;

    protected $sendHandle;

    protected $datas=[];

    protected $tmpExcelPath;

    protected $dateTimeLimit = [];

    public function __construct(array $config)
    {
        $this->config = new Config($config);

        //配置数据库信息
        if(empty($config['db'])){
            throw new \Exception('Please fill in the database configuration information');
        }
        $this->configDatabase($config['db']);
    }

    /**
     * 配置数据库连接
     */
    protected function configDatabase($config)
    {
        $this->dataHandle = new Datas($config);
    }

    /**
     *  记录上次的查询时间
     */
    public function setTimeLimit($tmp_filename = 'tmp_time.txt', $keyName = 'created_at')
    {

        $dateTimeTo = date('Y-m-d H:i:s', time());
        $dateTimeFrom = $this->configSyncLog($tmp_filename, $dateTimeTo);
        $this->dateTimeLimit = [
            [$keyName, '<=', $dateTimeTo],
            [$keyName, '>', $dateTimeFrom],
        ];
    }

    /**
     * 查询出对应的数据，用以生成Excel
     */
    public function getData($sql, $params = [])
    {
        if(!empty($this->dateTimeLimit)){
            if(strpos($sql, 'WHERE') === false){
                $sql .= ' WHERE 1 ';
            }
            foreach ($this->dateTimeLimit as $value){
                $sql .=  ' AND '.$value[0].$value[1].' ? ';
                $params =array_merge($params, [$value[2]]);
            }
        }
        $datas = $this->dataHandle->select($sql, $params);
        $this->datas = array_merge($this->datas, $datas);
        return $this->datas;
    }

    /**
     *  生成邮件
     *  params string $excelname 邮件名称
     *  return string 临时文件地址
     */
    public function createExcel($excelname = 'demo')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $AZ = range('A', 'Z');
        if(!empty($this->datas)){
            foreach ($this->datas as $ke=>$content){
                //$sheet->getColumnDimension($AZ[$ke])->setAutoSize(true);
                $ks = 0;
                foreach ($content as $k => $cell){
                    $sheet->setCellValue($AZ[$ks].($ke+1), $cell);
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

    /**
     *  发送邮件
     *  return 发送是否成功
     */
    public function send(array $receivers)
    {
        $config = $this->config['email']??[];
        $email = new Email($config);
        $email->setReceivers($receivers);
        $date = date('Y-m-d', time());
        $content = $date.'当天日用户核销统计，请查看附件详情';
        $email->setMessage('日统计报表', $content);


        $data = $this->createExcel(time());
        $data = file_get_contents($data);
        $formal = [
            'filename' => '欢乐谷统计报表.xls',
            'type'     => 'text/xml',
        ];
        //$email->attach($data);
        $email->attach($formal, $data);
        return $email->send();
    }
}