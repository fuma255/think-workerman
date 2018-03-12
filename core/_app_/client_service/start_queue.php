<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use \Workerman\Worker;
use \utils\Queue;
use GatewayClient\Gateway;


// 自动加载类
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'core/init.php';

Gateway::$registerAddress = \Config::$register['address'];

//消息队列 进程
$worker = new Worker();
// worker名称
$worker->name = 'MemCacheQueue';

$worker->count = 1;


$worker->onWorkerStart = function () {

    while (true) {

        $Queue = Queue::instance();

        while ($row = $Queue->get(Config::$app['http_port'])) {
            $task = json_decode($row, true);

            console('[QUEUE]:' . $task['_type'] . '|' . date('H:i:s', time()));

            switch ($task['_type']) {
                case 'sms_log':
                    //短信插入队列
                    unset($task['_type']);
                    \think\Db::table('sms_log')->insert($task);
                    break;
                default:
                    break;
            }
            flush();
        }

        sleep(1);
    }

};

// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}