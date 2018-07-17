<?php

namespace Hanson\Vbot\Core;

use Hanson\Vbot\Foundation\Vbot;
use Swoole\Process;
use Swoole\Server as SwooleServer;

class Swoole
{
    /**
     * @var Vbot
     */
    private $vbot;

    public function __construct(Vbot $vbot)
    {
        $this->vbot = $vbot;
    }

    public function run()
    {
        $server = new SwooleServer($this->vbot->config->get('swoole.ip', '0.0.0.0'), $this->vbot->config->get('swoole.port', 8866));

        $handleProcess = new Process(function ($worker) use (&$server) {
            $this->vbot->messageHandler->listen($server);
        });
        $pid = $handleProcess->start();

        $server->on('receive', function (SwooleServer $server, $fd, $from_id, $data) use ($pid) {
            $validData =  $this->vbot->api->validate($data);
            if($validData['params'] == 'exit'){
                Process::kill($pid);
                $msg = "消息监听进程{$pid}退出完成\n";
                file_put_contents('/tmp/msg.log', $msg);
                exec("echo  {$msg} >> /tmp/msg.log");
                $server->shutdown();
                $server->send($fd, 'exit sucess');
                return true;
            }
            $response = $this->vbot->api->handle($data);
            $response = $this->makeResponse($response);
            $server->send($fd, $response);
        });
        $server->start();
        exit;
    }

    private function validate($request)
    {
        $request = explode("\r\n\r\n", $request);

        if (!$request[1]) {
            return false;
        }

        $data = json_decode($request[1], true);

        if (!isset($data['action']) || !isset($data['params'])) {
            return false;
        }

        $namespace = '\\Hanson\\Vbot\\Api\\';

        if (class_exists($class = $namespace.ucfirst($data['action']))) {
            return ['params' => $data['params'], 'class' => 'api'.ucfirst($data['action'])];
        }

        return false;
    }

    private function makeResponse($data)
    {
        $data = json_encode($data);

        $headers = [
            'Server'         => 'Swoole',
            'Content-Type'   => 'application/json',
            'Content-Length' => strlen($data),
        ];

        $response[] = 'HTTP/1.1 200';

        foreach ($headers as $key => $val) {
            $response[] = $key.':'.$val;
        }

        $response[] = '';
        $response[] = $data;

        return implode("\r\n", $response);
    }
}
