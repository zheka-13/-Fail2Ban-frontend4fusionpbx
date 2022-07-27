<?php

class Fail2BanService
{
    private $config;


    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->config = include 'resources/config.inc.php';

    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        exec($this->config['bin']." version", $output);
        return $output[0];
    }

    /**
     * @return mixed
     */
    public function getJails()
    {
        exec($this->config['bin']." banned", $output);
        $line = implode("", $output);
        return json_decode(str_replace("'", '"', $line), true);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function check()
    {
        $this->checkRunning();
        $this->checkAccess();
    }

    public function getSocket()
    {
        return $this->config['socket'];
    }

    public function ban($jail, $ip)
    {
        if (empty($jail) || empty($ip)){
            return false;
        }
        if ($this->validateIp($ip)){
            exec($this->config['bin']." set ".$jail." banip ".$ip, $output);
            $line = trim(implode("", $output));
            if ($line == "1"){
                return true;
            }
        }

        return false;
    }

    public function unban($jail, $ip)
    {
        if (empty($jail) || empty($ip)){
            return false;
        }
        exec($this->config['bin']." set ".$jail." unbanip ".$ip, $output);
        $line = trim(implode("", $output));
        if ($line == "1"){
            return true;
        }
        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function checkAccess(){
        if (!is_writeable($this->config['socket'])){
            throw new Exception('no access');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function checkRunning(){
        if (!file_exists($this->config['socket'])){
            throw new Exception("stopped");
        }
    }

    private function validateIp($ip)
    {
        if (strstr($ip, "/") !== false){
            $tmp = explode("/", $ip);
            $ip = $tmp[0];
            $net = (int)$tmp[1];
            if ($net > 32 or $net < 24 ){
                return false;
            }
        }
        if (filter_var($ip,FILTER_VALIDATE_IP)){
            return true;
        }
        return false;
    }


}