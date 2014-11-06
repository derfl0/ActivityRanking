<?php
require 'bootstrap.php';

/**
 * ActivityrankingPlugin.class.php
 *
 * ...
 *
 * @author  Florian Bieringer <florian.bieringer@uni-passau.de>
 * @version 1.0
 */

class ActivityrankingPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();

        $navigation = new AutoNavigation(_('Rangliste (Aktivität)'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
        Navigation::addItem('/community/activityrankingplugin', $navigation);

        PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
        PageLayout::addScript($this->getPluginURL().'/assets/application.js');
    }

    public function initialize () {

    }

    public function perform($unconsumed_path) {
        
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    private function setupAutoload() {
        if (class_exists("StudipAutoloader")) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
    
    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        
        require 'models/ActivityScore.php';
        // Publish those users that had their studip score published
        $users = DBManager::get()->query('SELECT user_id FROM user_info WHERE score > 0');
        while ($user = $users->fetchColumn()) {
            $as = new ActivityScore($user);
            $as->public = 1;
            
            //recalc
            $as->score;
            $as->store();
        }
    }
}
