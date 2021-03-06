<?php namespace Nosun\Swoole\ServerManager;

use Nosun\Swoole\Contract\ServerManagerContract as ServerContract;

abstract class BaseServerManager implements ServerContract {

    protected $server;                       // swoole_server
    protected $processName = 'swooleServer'; // default process name
    protected $host = '0.0.0.0';            // default host
    protected $port = 9501;                 // default port
    protected $listen;                      // listen port
    protected $mode = SWOOLE_PROCESS;       // ??
    protected $mainSetting = array();       // main config
    protected $serverSetting = array(       // server setting
              'worker_num' => 4,            // worker process num
              'backlog' => 128,             // listen backlog
              );
    protected $runPath = '/tmp';            // pid save path
    protected $masterPidFile;               // master pid file
    protected $managerPidFile;              // manager pid file
    protected $user;                        // process user
    protected $serverType ='swoole_server'; // http server
    protected $socketType = SWOOLE_SOCK_TCP;
    protected $protocol;                      // protocol
    protected $preSysCmd = '%+-swoole%+-';    // pre sys cmd
    protected $serverClass;                   // server Class ??
    protected $udp;

	function __construct($conf)
	{
        // 设定默认配置
        $this->mainSetting = $conf['main'];
        $this->serverSetting = array_merge($this->serverSetting,$conf['server']);
	}


    /*
    |--------------------------------------------------------------------------
    | 初始化 Server
    |--------------------------------------------------------------------------
    |
    | 设置进程名，pid，app类，user,监听端口等信息
    |
    */

    protected function _initRunTime()
    {
        $this->processName    = $this->mainSetting['process_name'] ? $this->mainSetting['process_name']:$this->processName;
        $this->runPath        = $this->mainSetting['run_path'] ? $this->mainSetting['run_path']:$this->runPath;
        $this->masterPidFile  = $this->runPath . '/' . $this->processName . '.master.pid';
        $this->managerPidFile = $this->runPath . '/' . $this->processName . '.manager.pid';
        $this->serverClass    = $this->mainSetting['server_class'] ? $this->mainSetting['server_class'] : '';

        // set user
        if (isset($this->mainSetting['user']))
        {
            $this->user = $this->mainSetting['user'];
        }

    }


    /*
    |--------------------------------------------------------------------------
    | 初始化 swoole 配置
    |--------------------------------------------------------------------------
    |
    |  create swoole server，set server，set callback function,add listener,
    |  @override in sub class,because there is many different type of server;
    |
    |  socket Server
    |  http server
    |  webSocket server
    |
    */

    protected function initServer() {

        // $this->setServerType();

        if ($this->mainSetting['listen'])
        {
            $this->host = $this->mainSetting['listen']['host'] ? $this->mainSetting['listen']['host'] : $this->host;
            $this->port = $this->mainSetting['listen']['port'] ? $this->mainSetting['listen']['port'] : $this->port;
            if(isset($this->mainSetting['listen']['socketType']) && !empty($this->mainSetting['listen']['socketType'])){
                $this->socketType = constant($this->mainSetting['listen']['socketType']);
            }
        }

        switch($this->serverType){

            case 'socket':
                $this->server = new \swoole_server($this->host, $this->port, $this->mode, $this->socketType);
                break;
            case 'socket_ssl':
                $this->server = new \swoole_server($this->host, $this->port, $this->mode, SWOOLE_SOCK_TCP | SWOOLE_SSL);
                break;
	        case 'http':
                $this->server = new \swoole_http_server($this->host, $this->port,$this->mode);
                break;
            case 'websocket':
                $this->server = new \swoole_websocket_server($this->host, $this->port,$this->mode);
                break;
            case 'websocket_ssl':
                $this->server = new \swoole_websocket_server($this->host, $this->port,$this->mode, SWOOLE_SOCK_TCP | SWOOLE_SSL);
                break;
            default:
                exit('serverType is not support');
        }

        // Setting the runtime parameters
        $this->server->set($this->serverSetting);

        // Set Event Server callback function
        $this->server->on('Start', array($this, 'onMasterStart'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->server->on('WorkerStop', array($this, 'onWorkerStop'));

        if (isset($this->serverSetting['task_worker_num'])) {
            $this->server->on('Task', array($this, 'onTask'));
            $this->server->on('Finish', array($this, 'onFinish'));
        }

        $this->addCallback();

        if(isset($this->mainSetting['listener']) && count($this->mainSetting['listener']) > 0 ){
            $listeners = $this->mainSetting['listener'];
            $this->addListener($listeners);
        }


    }

    // sun class implements
    protected function addCallback(){}

    protected function addListener($listeners){
        foreach($listeners as $listener){
            $host = isset($listener['host']) ? $listener['host'] : $this->host;
            $port = isset($listener['port']) ? $listener['port'] : '';
            $socketType = isset($listener['socketType']) ? $listener['socketType'] : 'SWOOLE_SOCK_TCP';
            $ssl  = isset($listener['ssl']) ? $listener['ssl'] : false;

            if(empty($host) ||  empty($port) || empty($socketType)){
                continue;
            }

            if($ssl){
                $this->server->addlistener($host, $port, SWOOLE_SOCK_TCP | SWOOLE_SSL); // websocket http tcp
            }else{
                $this->server->addlistener($host, $port, constant($socketType)); // websocket , tcp, udp, http
            }

        }
    }

    /*
    |--------------------------------------------------------------------------
    | onMasterStart
    |--------------------------------------------------------------------------
    | 设置进程名字，pid存储，user。
    |
    */

    public function onMasterStart($server){

        Console::setProcessName($this->processName.': master process');
        file_put_contents($this->masterPidFile, $server->master_pid);
        file_put_contents($this->managerPidFile, $server->manager_pid);
        if ($this->user)
        {
            Console::changeUser($this->user);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | onManagerStart
    |--------------------------------------------------------------------------
    | 设置进程名字，pid存储，user。
    |
    */

    public function onManagerStart($server)
    {
        Console::setProcessName($this->processName.': manager process');
        if ($this->user)
        {
            Console::changeUser($this->user);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | onWorkerStart
    |--------------------------------------------------------------------------
    | Worker 进程启动时调用，worker分为两种，event worker，task worker。
    | 设置worker的名子，设置user，在worker中增加定时器。
    | 在worker启动的过程中，载入protocol类中的回调函数，处理实际业务逻辑。
    |
    */
    public function onWorkerStart($server, $workerId)
    {
        if($workerId >= $this->serverSetting['worker_num'])
        {
            Console::setProcessName($this->processName.': task worker process');
        }
        else
        {
            Console::setProcessName($this->processName.': event worker process');
        }

        if ($this->user)
        {
            Console::changeUser($this->user);
        }

        // 将用户设定的回调函数类实例化 important!
        if ($this->serverClass && class_exists($this->serverClass))
        {
            $this->setProtocol(new $this->serverClass());
        }

        // check protocol class
        if (! $this->protocol)
        {
            throw new \Exception("[error] the protocol class " . $this->serverClass . " is empty or undefined");
        }

        // 在 protocol 类中设置onStart 回调
        $this->protocol->onStart($server, $workerId);
    }

    public function onWorkerStop($server, $workerId)
    {
        $this->protocol->onShutdown($server, $workerId);
    }

    public function onTask($server, $taskId, $fromId, $data)
    {
        $this->protocol->onTask($server, $taskId, $fromId, $data);
    }

    public function onFinish($server, $taskId, $data)
    {
        $this->protocol->onFinish($server, $taskId, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | setProtocol 设置 回调函数类
    |--------------------------------------------------------------------------
    |
    | app下的具体的Server 类，业务逻辑类，在此引入
    |
    */

	public function setProtocol($protocol){

        $this->checkProtocol($protocol);
        $this->protocol = $protocol;

        $protocol->server = $this;
		$this->protocol = $protocol;
        $this->protocol->server = $this->server;
	}

    protected function checkProtocol($protocol)
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | run 启动方法
    |--------------------------------------------------------------------------
    |
    | start, stop,reload,restart,status
    |
    */

    public function run(){

        $this->_initRunTime();
        $cmd = isset($_SERVER['argv'][1]) ? strtolower($_SERVER['argv'][1]) : 'help';

        switch ($cmd) {
            case 'stop':
                $this->shutdown();
                break;
            case 'start':
                $this->initServer();
                $this->start();
                break;
            case 'restart':
                sleep(1);
                $this->shutdown();
                sleep(1);
                $this->initServer();
                $this->start();
                break;
            case 'status':
                $this->status();
                break;
            default:
                echo 'Usage: php start.php start | stop | restart | status | help' . PHP_EOL;
                break;
        }
    }


    protected function start(){

       if ($this->checkServerIsRunning()) {
           $this->log("[warning] " . $this->processName . ": master process file " . $this->masterPidFile . " has already exists!");
           $this->log($this->processName . ": start\033[31;40m [OK] \033[0m");
           return false;
       }
       $this->log($this->processName . ": start\033[31;40m [OK] \033[0m");
       $this->server->start();
       return true;
   }


    public function shutdown(){

        $masterId = $this->getMasterPid();
        if (! $masterId) {
            $this->log("[warning] " . $this->processName . ": can not find master pid file");
            $this->log($this->processName . ": stop\033[31;40m [FAIL] \033[0m");
            return false;
        }
        elseif (! posix_kill($masterId, 15))
        {
            $this->log("[warning] " . $this->processName . ": send signal to master failed");
            $this->log($this->processName . ": stop\033[31;40m [FAIL] \033[0m");
            return false;
        }
        unlink($this->masterPidFile);
        unlink($this->managerPidFile);
        $this->log($this->processName . ": stop\033[31;40m [OK] \033[0m");
        return true;
    }

    protected function status(){

        $this->log("*****************************************************************");
        $this->log("Summary: ");
        $this->log("Swoole Version: " . SWOOLE_VERSION);
        if (! $this->checkServerIsRunning()) {
            $this->log($this->processName . ": is running \033[31;40m [FAIL] \033[0m");
            $this->log("*****************************************************************");
            return false;
        }
        $this->log($this->processName . ": is running \033[31;40m [OK] \033[0m");
        $this->log("master pid : is " . $this->getMasterPid());
        $this->log("manager pid : is " . $this->getManagerPid());
        $this->log("*****************************************************************");
        return true;
    }

    protected function getMasterPid() {
        $pid = false;
        if (file_exists($this->masterPidFile)) {
            $pid = file_get_contents($this->masterPidFile);
        }
        return $pid;
    }

    protected function getManagerPid() {
        $pid = false;
        if (file_exists($this->managerPidFile)) {
            $pid = file_get_contents($this->managerPidFile);
        }
        return $pid;
    }

    protected function checkServerIsRunning() {
        $pid = $this->getMasterPid();
        return $pid && $this->checkPidIsRunning($pid);
    }

    protected function checkPidIsRunning($pid) {
        return posix_kill($pid, 0);
    }


    /*
    |--------------------------------------------------------------------------
    | log 方法
    |--------------------------------------------------------------------------
    |
    | start, stop,reload,restart,status
    |
    */


    public function log($msg){

        if ($this->serverSetting['log_file'] && file_exists($this->serverSetting['log_file']))
        {
            error_log($msg . PHP_EOL, 3, $this->serverSetting['log_file']);
        }
        echo $msg . PHP_EOL;
    }

    /*
    |--------------------------------------------------------------------------
    | transListener 处理 listener 配置
    |--------------------------------------------------------------------------
    |
    | 将 listen 变量 host:port 的形式转换成
    |
    | array array(
    |     host=>$host,
    |     port=>$port
    | )
    |
    */

    protected function transListener($listen){

        if(!is_array($listen)){

            $tmpArr = explode(":", $listen);
            $host = isset($tmpArr[2]) ? $tmpArr[0] : $this->host;
            $port = isset($tmpArr[2]) ? $tmpArr[1] : $tmpArr[0];
            $type = isset($tmpArr[2]) ? $tmpArr[2] : $tmpArr[1];

            $this->listen[] = array(
                'host' => $host,
                'port' => $port,
                'type' => $type
            );
            // 此处需要设置跳出，否则还会向下执行;
            return true;
        }

        foreach($listen as $v){
            $this->transListener($v);
        }
        return true;
    }

    public function close($client_id){

        $this->server->close($client_id);
    }

    public function send($client_id, $data){

        $this->server->send($client_id, $data);
    }

}



