<h1 align="center"> sendemail </h1>

<p align="center"> Timed tasks automatically query data and send it to the specified mailbox.</p>


## Installing

```shell
$ composer require lyignore/sendemail -vvv
```

## Usage

TODO
```shell
// 引入composer中的组件
require __DIR__ .'/vendor/autoload.php';
//配置环境
$config = [
    'db' =>[
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '33060',
        'DB_DATABASE' => 'test',
        'DB_USERNAME' => 'homestead',
        'DB_PASSWORD' => '',
    ],
    'email' => [
        'EMAIL_SENDSERVER'=>'smtp.mxhichina.com',
        'EMAIL_USERNAME' => 'wangyue@aikk.com.cn',
        'EMAIL_PASSWORD' => 'Aikaka12',
        'EMAIL_PORT' => '465',
        'EMAIL_TYPE' => 'ssl',
    ],
];
$sendemail = new \Lyignore\Sendemail\SendEmail($config);

// 判断是查询从上次到这次期间生成的数据还是查询全部的数据，test.txt为临时文件名称，可自定义
$sendemail->setTimeLimit('test.txt');
// 查询的sql语句，预查询，可传入第二个参数，SQL语句用 ？代替变量
$datas = $sendemail->getData("SELECT * FROM `users` WHERE `email` = ? ", ['admin']);
// 按照配置自动发送邮件， 可直接配置邮箱，也可用别名
$receivers = ['liu@aikk.com.cn', 'notify@aikk.com.cn' => 'test'];
$res = $sendemail->send($receivers);

// 也可以按照查询的数据生成Excel文件，SendEmail->send()会自动调用生成Excel方法，返回Excel的临时路径
$path = $sendemail->createExcel();
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/lyignore/sendemail/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/lyignore/sendemail/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT