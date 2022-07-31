<?php

class Fail2BanService
{
    private $config;

    private $start_time = 0;

    private $mailService;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->config = include 'resources/config.inc.php';
        $this->start_time = microtime(true);
        $this->mailService = new MailService($this->config['emails']);
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
     * @return float
     */
    public function getDiffTime()
    {
        return round((microtime(true) - $this->start_time), 4);
    }

    /**
     * @return mixed
     */
    public function getJails()
    {
        exec($this->config['bin']." banned", $output);
        $line = implode("", $output);
        $jails = json_decode(str_replace("'", '"', $line), true);
        $new_jails = [];
        foreach ($jails as $jail){
            foreach ($jail as $jail_name => $ips){
                $new_jails[$jail_name] = [];
                foreach ($ips as $ip){
                    $new_jails[$jail_name][] = [
                        "ip" => $ip,
                        "domain" => ($this->config['use_dns']  ? gethostbyaddr($ip) : null)
                    ];
                }
            }
        }
        return $new_jails;
    }

    public function addToWhitelist($ip)
    {
        if (!$this->validateIp($ip)){
            return false;
        }
        $content  = file_get_contents($this->config['conf']);
        $conf_line = "";
        $conf_index = 0;
        $lines = explode("\n", $content);
        foreach ($lines as $key => $line){
            if (strpos($line, "ignoreip") !== false && substr($line, 0, 1) !== "#"){
                $conf_line = $line;
                $conf_index = $key;
                break;
            }
        }
        $ips =  $this->getIpsFromString($conf_line);
        $ips[] = $ip;
        if ($conf_index == 0){
            foreach ($lines as $key => $line){
                if (strpos($line, "#ignoreip") !== false){
                    $conf_index = $key;
                    break;
                }
            }
        }
        if ($conf_index == 0){
            foreach ($lines as $key => $line){
                if (trim($line) == "[DEFAULT]"){
                    array_splice( $lines, $key+1, 0, "");
                    $conf_index = $key+1;
                    break;
                }
            }
        }
        if ($conf_index == 0){
            $lines[] = "[DEFAULT]";
            $lines[] = "";
            $conf_index = count($lines)-1;
        }
        if ($conf_index == 0){
            return false;
        }

        $lines[$conf_index] = "ignoreip = ".implode(" ", $ips);
        file_put_contents($this->config['conf'], implode("\n", $lines));
        $this->reload();
        $this->mailService->addedToWhitelist($ip);
        return true;
    }

    public function removeFromWhtelist($ip)
    {
        if (!$this->validateIp($ip)){
            return false;
        }
        $content  = file_get_contents($this->config['conf']);
        $conf_line = "";
        $conf_index = 0;
        $lines = explode("\n", $content);
        foreach ($lines as $key => $line){
            if (strpos($line, "ignoreip") !== false && substr($line, 0, 1) !== "#"){
                $conf_line = $line;
                $conf_index = $key;
                break;
            }
        }
        if (empty($conf_index)){
            return false;
        }
        $ips =  $this->getIpsFromString($conf_line);
        if (($ip_key = array_search($ip, $ips)) !== false) {
            unset($ips[$ip_key]);
        }
        $lines[$conf_index] = "ignoreip = ".implode(" ", $ips);
        file_put_contents($this->config['conf'], implode("\n", $lines));
        $this->reload();
        $this->mailService->removedFromWhitelist($ip);
        return true;
    }

    public function getWhitelistIps()
    {
        $content  = file_get_contents($this->config['conf']);
        $conf_line = "";
        $lines = explode("\n", $content);
        foreach ($lines as $line){
            if (strpos($line, "ignoreip") !== false && substr($line, 0, 1) !== "#"){
                $conf_line = $line;
                break;
            }
        }
        $ips =  $this->getIpsFromString($conf_line);
        $new_ips = [];
        foreach ($ips as $ip){
            $new_ips[] = [
                "ip" => $ip,
                "domain" => ($this->config['use_dns']  ? gethostbyaddr($ip) : null)
            ];
        }
        return $new_ips;
    }

    /**
     * @throws Exception
     */
    public function whitelistCheck()
    {
        if (!is_file($this->config['conf'])){
            throw new Exception("no_conf");
        }
        if (!is_writable($this->config['conf'])){
            throw new Exception("no_conf");
        }
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

    public function getConf()
    {
        return $this->config['conf'];
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
                $this->mailService->addedToBlacklist($ip);
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
            $this->mailService->removedFromBlacklist($ip);
            return true;
        }
        return false;
    }

    private function reload()
    {
        exec($this->config['bin']." reload");
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

    /**
     * @param $line
     * @return array
     */
    private function getIpsFromString($line)
    {
        if (empty($line)){
            return [];
        }
        $tmp = explode(" ", preg_replace("/\s+/", " ", $line));
        $ips = [];
        foreach ($tmp as $ip){
            if (strpos($ip, "ignore") !== false){
                continue;
            }
            if (strpos($ip, "=") !== false){
                continue;
            }
            $ips[] = trim($ip);
        }
        return $ips;
    }






}