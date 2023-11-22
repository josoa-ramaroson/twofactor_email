<?php 

namespace OCA\TwoFactor_Email\Service;

use OCP\IConfig;
use OCP\IUser;
use OCP\IGroupManager;

class ExcludedGroupService{

    /** @var IConfig */
    public $config;

    /** @var IGroupManager */
    public $groupManager;
    public function __construct(IConfig $config, IGroupManager $groupManager)
    {
        $this->config = $config;   
        $this->groupManager = $groupManager; 
    } 

    public function createGroupExcluded($gid){
        if(!$this->groupManager->groupExists($gid))
            $this->groupManager->createGroup($gid);

		$this->addGroupExcluded($gid);
    }

    public function addGroupExcluded($gid){
        $excludedGroup =json_decode($this->config->getAppValue("core","enforce_2fa_excluded_groups"));
        if( !in_array($gid,$excludedGroup) ){
            $excludedGroup[] = $gid;
            $excludedGroupString = json_encode($excludedGroup);
            $this->config->setAppValue("core","enforce_2fa_excluded_groups",$excludedGroupString);
        }
    }
   
    public function userIncluded(IUser $user){
        // 2FA is enforced for all users
        $userGroupIds = $this->groupManager->getUserGroupIds($user);
        
        $excludedGroup =json_decode($this->config->getAppValue("core","enforce_2fa_excluded_groups"));
        $enabled = true;
        foreach($userGroupIds as $gid){
            foreach($excludedGroup as $exgid){
                if($gid === $exgid){
                    $enabled = false;
                    break;
                }
            }
        }

        return $enabled;
    }
}