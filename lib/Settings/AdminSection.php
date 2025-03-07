<?php
namespace OCA\DienstzeitenApp\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
    /** @var IL10N */
    private $l;
    
    /** @var IURLGenerator */
    private $urlGenerator;
    
    public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
        $this->l = $l;
        $this->urlGenerator = $urlGenerator;
    }
    
    public function getID() {
        return 'dienstzeiten_app';
    }
    
    public function getName() {
        return $this->l->t('Dienstzeiten');
    }
    
    public function getPriority() {
        return 50;
    }
    
    public function getIcon() {
        return $this->urlGenerator->imagePath('dienstzeiten_app', 'app-icon.svg');
    }
}
