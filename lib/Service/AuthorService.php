<?php
namespace OCA\TwoFactor_Email\Service;
use \OCP\IConfig;


class AuthorService {
    private $appName;
    
    private $config;

  
    public function __construct(IConfig $config, $appName){
        $this->config = $config;
        $this->appName = $appName;
    }

    public function getSystemValue($key) {
        return $this->config->getSystemValue($key);
    }

    public function setSystemValue($key, $value) {
        $this->config->setSystemValue($key, $value);
    }

    public function getAppValue($key) {
        return $this->config->getAppValue($this->appName, $key);
    }

    public function setAppValue($key, $value) {
        $this->config->setAppValue($this->appName, $key, $value);
    }

    public function getUserValue($key, $userId) {
        return $this->config->getUserValue($userId, $this->appName, $key);
    }
    
    public function setUserValue($key, $userId, $value) {
        $this->config->setUserValue($userId, $this->appName, $key, $value);
    }



}