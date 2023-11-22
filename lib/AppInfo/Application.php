<?php
namespace OCA\TwoFactor_Email\AppInfo;

use \OCP\AppFramework\App;


class Application extends App {

    public const APP_NAME = 'twofactor_email';

    /** @var IConfig */
    public $config;
    public function __construct(array $urlParams=array()){
        
        parent::__construct(self::APP_NAME, $urlParams);
        $container = $this->getContainer();
      
        $container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });
    }
}