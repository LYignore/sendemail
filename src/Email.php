<?php
namespace Lyignore\Sendemail;

use Lyignore\Sendemail\Contracts\EmailInterface;

class Email implements EmailInterface{
    protected $config;

    protected $transportHandle;

    protected $messageHandle;

    protected $attachHandle;

    protected $mailer;

    protected $allReceivers = [];

    protected $addresser;

    public function __construct($config)
    {
        $this->config = array(
            'sending_server' => $config['EMAIL_SENDSERVER']??'smtp.mxhichina.com',
            'username'   => $config['EMAIL_USERNAME']??'root',
            'password' => $config['EMAIL_PASSWORD']??'',
            'port'   => $config['EMAIL_PORT']??'465',
            'encryption_type' => $config['EMAIL_TYPE']??'ssl',
        );

        $this->configTransport();
    }

    /**
     * 配置发送邮件服务器
     */
    public function configTransport()
    {
        try{
            $transport = new \Swift_SmtpTransport($this->config['sending_server'], $this->config['port'], $this->config['encryption_type']);
            $this->transportHandle = $transport
                ->setUsername($this->config['username'])
                ->setPassword($this->config['password']);
        }catch (\Exception $e){
            throw new \Exception('Mail server authentication failed');
        }
    }

    public function buildMailer(){
        if(!$this->transportHandle instanceof \Swift_SmtpTransport){
            $this->configTransport();
        }
        $this->mailer = new \Swift_Mailer($this->transportHandle);
    }

    /**
     *  设置接受者的邮箱
     *  params array 用户的邮箱=>显示的别名
     *  return array  Total set of receivers
     */
    public function setReceivers(array $receivers){
        $this->allReceivers = array_merge($this->allReceivers, $receivers);
        if($this->messageHandle instanceof \Swift_Message){
            $this->messageHandle->setTo($this->allReceivers);
        }
        return $this->allReceivers;
    }

    /**
     *  设置发送人邮箱和别名
     */
    protected function setAddresser($name = null)
    {
        if(is_null($name)){
            $arr = explode("@", $this->config['username']);
            $email = [$this->config['username'] => $arr[0]];
        }else{
            $email = [$this->config['username'] => $name];
        }
        $this->addresser = $email;
        if($this->messageHandle instanceof \Swift_Message){
            $this->messageHandle->setFrom($this->addresser);
        }
    }

    /**
     *  设置发送模板
     */
    public function setMessage($subject, $content, $attach=null)
    {
        $this->buildMailer();
        $this->messageHandle = (new \Swift_Message($subject))
            ->setBody($content);
        if(!empty($this->allReceivers)){
            $this->messageHandle->setTo($this->allReceivers);
        }
        $this->setAddresser('aikaka');
        if(!is_null($attach)){
            $this->messageHandle->attach($attach);
        }
    }

    public function attach($path, $data=null){
        if(is_array($path) && !is_null($data)){
            $filename = $path['filename']??'demo.xls';
            $type = $path['type']??'text/xml';
            $this->attachHandle = (new \Swift_Attachment())
                ->setFilename($filename)
                ->setContentType($type)
                ->setBody($data);
        }else{
            $this->attachHandle = \Swift_Attachment::fromPath($path);
        }
        if($this->messageHandle instanceof \Swift_Message){
            $this->messageHandle->attach($this->attachHandle);
        }
        return $this->attachHandle;
    }

    public function send()
    {
        if(!$this->mailer instanceof \Swift_Mailer){
            throw new \Exception('The send object is not configured');
        }
        return $this->mailer->send($this->messageHandle);
    }
}