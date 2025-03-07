<?php
namespace OCA\DienstzeitenApp\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\IGroupManager;

class Admin implements ISettings {
    /** @var IConfig */
    private $config;

    /** @var string */
    private $appName;

    /** @var IGroupManager */
    private $groupManager;

    public function __construct(string $appName, IConfig $config, IGroupManager $groupManager) {
        $this->appName = $appName;
        $this->config = $config;
        $this->groupManager = $groupManager;
    }

    public function getForm() {
        $teamleadGroup = $this->config->getAppValue($this->appName, 'teamlead_group', '');
        $hrEmail = $this->config->getAppValue($this->appName, 'hr_email', '');

        $groups = $this->groupManager->search('');
        $groupOptions = [];
        foreach ($groups as $group) {
            $groupOptions[] = [
                'id' => $group->getGID(),
                'name' => $group->getDisplayName()
            ];
        }

        $parameters = [
            'teamlead_group' => $teamleadGroup,
            'hr_email' => $hrEmail,
            'groups' => $groupOptions
        ];

        return new TemplateResponse($this->appName, 'settings', $parameters, 'blank');
    }

    public function getSection() {
        return 'dienstzeiten_app';
    }

    public function getPriority() {
        return 10;
    }
}
