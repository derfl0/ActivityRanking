<?php

class ShowController extends StudipController {

    public function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args) {

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
//      PageLayout::setTitle('');
        // Fetch my score
        $this->score = new ActivityScore($GLOBALS['user']->id);
    }

    public function index_action($page = 1) {

        Navigation::activateItem('/community/activityrankingplugin');

        $this->setInfobox();

        // Fetch other scores
        $count = ActivityScore::countBySql("public = 1");

        // Calculate offsets
        $max_per_page = get_config('ENTRIES_PER_PAGE');
        $max_pages = ceil($count / $max_per_page);

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $max_pages) {
            $page = $max_pages;
        }

        $offset = max(0, ($page - 1) * $max_per_page);

        $this->scores = ActivityScore::findBySQL("public = 1 ORDER BY score DESC LIMIT ?,?", array((int) $offset, (int) $max_per_page));
        $this->scores = SimpleORMapCollection::createFromArray($this->scores);

        // Reorder if score had to be updated
        $this->scores->orderBy('score DESC');

        $this->numberOfPersons = $count;
        $this->page = $page;
        $this->offset = $offset;
        $this->max_per_page = $max_per_page;
    }

    private function setInfobox() {
        // Define infobox
        $this->setInfoboxImage('infobox/board2.jpg');
        $this->addToInfobox(_('Ihre Position:'), _('Ihre Punkte: ') .
                '<strong>' . number_format($this->score->score, 0, ',', '.') . '</strong>');
        $this->addToInfobox(_('Ihre Position:'), _('Ihr Titel: ') . '<strong>' . $this->score->title . '</strong>');
        $this->addToInfobox(_('Information:'), _('Auf dieser Seite können Sie abrufen, wie weit Sie in der '
                        . 'Stud.IP-Rangliste aufgestiegen sind. Je aktiver Sie sich '
                        . 'im System verhalten, desto höher klettern Sie!'), 'icons/16/black/info.png');
        $this->addToInfobox(_('Information:'), _('Sie erhalten auf der Profilseite von MitarbeiternInnen an '
                        . 'Einrichtungen auch weiterführende Informationen, wie '
                        . 'Sprechstunden und Raumangaben.'), 'icons/16/black/info.png');

        if ($this->score->public) {
            $icon = 'icons/16/black/remove/crown.png';
            $action = sprintf('<a href="%s">%s</a>', $this->url_for('show/unpublish'), _('Ihren Wert von der Liste löschen'));
        } else {
            $icon = 'icons/16/black/add/crown.png';
            $action = sprintf('<a href="%s">%s</a>', $this->url_for('show/publish'), _('Diesen Wert auf der Liste veröffentlichen'));
        }
        $this->addToInfobox(_('Aktionen:'), $action, $icon);
    }

    /**
     * Publish user's score / add user's score to the ranking list.
     */
    public function publish_action() {
        $this->score->public = 1;
        $this->score->store();
        PageLayout::postMessage(MessageBox::success(_('Ihr Wert wurde auf der Rangliste veröffentlicht.')));
        $this->redirect('show');
    }

    /**
     * Removes the user's score from the ranking list.
     */
    public function unpublish_action() {
        $this->score->public = 0;
        $this->score->store();
        PageLayout::postMessage(MessageBox::success(_('Ihr Wert wurde von der Rangliste gelöscht.')));
        $this->redirect('show');
    }

    // customized #url_for for plugins
    function url_for($to) {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }

}
