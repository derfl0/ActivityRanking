<?php

/**
 * Score.php
 * model class for table Score
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class ActivityScore extends SimpleORMap {

    // How often should the score be updated
    const UPDATE_INTERVAL = 1;
    
    // How long should an activity block others?
    const MEASURING_STEP = 1800; // half an hour
    
    // Timesstamp query cache
    private static $timestampQuery;
            
      public function __construct($id = null) {
          $this->db_table = 'user_activity_score';
          $this->additional_fields['title'] = true;
          $this->belongs_to['user'] = array(
              'class_name' => 'User'
          );
          parent::__construct($id);
      }

    /**
     * Returns the score for a user (Refresh score if it is to old)
     * 
     * @return int score
     */
    public function getScore() {
        if (!$this->content['score'] || (time() - $this->chdate) > static::UPDATE_INTERVAL) {
            $this->content['score'] = $this->calculate();
            $this->store();
        }
        return $this->content['score'];
    }

    /**
     * Get kings of a score
     * 
     * @return mixed Kings
     */
    public function getKing() {
        return StudipKing::is_king($this->user_id, true);
    }

    /**
     * Returns the title of a scorer
     * 
     * @return String title
     */
    public function getTitle() {
        $allTitle = self::getAllTitle();
        $title = min(array(count($allTitle), floor(log10($this->score) / log10(2))));
        return $allTitle[$title][$this->user->info->geschlecht];
    }

    /**
     * Returns array with all titles
     * 
     * @return array titles
     */
    protected static function getAllTitle() {
        return array(
            array(1 => _("Unbeschriebenes Blatt"), 2 => _("Unbeschriebenes Blatt")),
            array(1 => _("Unbeschriebenes Blatt"), 2 => _("Unbeschriebenes Blatt")),
            array(1 => _("Unbeschriebenes Blatt"), 2 => _("Unbeschriebenes Blatt")),
            array(1 => _("Neuling"), 2 => _("Neuling")),
            array(1 => _("Greenhorn"), 2 => _("Greenhorn")),
            array(1 => _("Anfänger"), 2 => _("Anfängerin")),
            array(1 => _("Einsteiger"), 2 => _("Einsteigerin")),
            array(1 => _("Beginner"), 2 => _("Beginnerin")),
            array(1 => _("Novize"), 2 => _("Novizin")),
            array(1 => _("Fortgeschrittener"), 2 => _("Fortgeschrittene")),
            array(1 => _("Kenner"), 2 => _("Kennerin")),
            array(1 => _("Könner"), 2 => _("Könnerin")),
            array(1 => _("Profi"), 2 => _("Profi")),
            array(1 => _("Experte"), 2 => _("Expertin")),
            array(1 => _("Meister"), 2 => _("Meisterin")),
            array(1 => _("Großmeister"), 2 => _("Großmeisterin")),
            array(1 => _("Idol"), 2 => _("Idol")),
            array(1 => _("Guru"), 2 => _("Hohepriesterin")),
            array(1 => _("Lichtgestalt"), 2 => _("Lichtgestalt")),
            array(1 => _("Halbgott"), 2 => _("Halbgöttin")),
            array(1 => _("Gott"), 2 => _("Göttin")),
        );
    }

    /**
     * Calculates the score for a user. Will pass the function on to the first
     * ScorePlugin, that is found
     * 
     * @return int
     */
    private function calculate() {
        if (false) {
            //old code
            $sql = "
                SELECT round(SUM((-atan(((unix_timestamp() / ".self::MEASURING_STEP.") - dates) / ".round(31556926 / self::MEASURING_STEP) .") / PI() + 0.5) * 1000)) as score FROM (
                SELECT distinct (round(mkdate / ".self::MEASURING_STEP."))  as dates from
                (
                SELECT mkdate FROM dokumente WHERE user_id = :user
                UNION
                SELECT mkdate FROM seminar_user WHERE user_id = :user
                UNION
                SELECT mkdate FROM user_info WHERE user_id = :user
                UNION
                SELECT mkdate FROM news WHERE user_id = :user
                UNION
                SELECT mkdate FROM kategorien WHERE range_id = :user
                UNION
                SELECT mkdate FROM vote WHERE range_id = :user
                UNION
                SELECT votedate as mkdate FROM vote_user WHERE user_id = :user
                UNION
                SELECT votedate as mkdate FROM voteanswers_user WHERE user_id = :user
                UNION
                SELECT chdate as mkdate FROM wiki WHERE user_id = :user
                UNION
                SELECT mkdate FROM blubber WHERE user_id = :user
                ) as mkdates) as dates";
        } else {
            //my approach
            $sql = "
                SELECT round(SUM((-atan(measurement / ".round(31556926 / self::MEASURING_STEP) .") / PI() + 0.5) * 1000)) as score
                FROM (
                    SELECT ((unix_timestamp() / ".self::MEASURING_STEP.") - timeslot) / SQRT(weigh) AS measurement
                    FROM (
                        SELECT (round(mkdate / ".self::MEASURING_STEP.")) as timeslot, COUNT(*) AS weigh
                        FROM (
                            SELECT mkdate FROM dokumente WHERE user_id = :user
                            UNION
                            SELECT mkdate FROM seminar_user WHERE user_id = :user
                            UNION
                            SELECT mkdate FROM user_info WHERE user_id = :user
                            UNION
                            SELECT mkdate FROM news WHERE user_id = :user
                            UNION
                            SELECT mkdate FROM kategorien WHERE range_id = :user
                            UNION
                            SELECT mkdate FROM vote WHERE range_id = :user
                            UNION
                            SELECT votedate as mkdate FROM vote_user WHERE user_id = :user
                            UNION
                            SELECT votedate as mkdate FROM voteanswers_user WHERE user_id = :user
                            UNION
                            SELECT chdate as mkdate FROM wiki WHERE user_id = :user
                            UNION
                            SELECT mkdate FROM blubber WHERE user_id = :user
                        ) as mkdates
                        GROUP BY timeslot
                    ) as measurements
                ) as dates
            ";
        }
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array(':user' => $this->user_id));
        return $stmt->fetchColumn();
    }
    
    private static function createTimestampQuery() {
        if (!self::$timestampQuery) {
            $tables = DBManager::get()->query('SELECT * FROM user_activity_tables');
            $statements = array();
            while ($table = $tables->fetch(PDO::FETCH_ASSOC)) {
                $statements[] = "SELECT "
                        .($table['datecol'] ? : 'mkdate')
                        ." AS mkdate FROM "
                        .$table['table']
                        ." WHERE "
                        .($table['usercol'] ? : 'user_id')
                        ." = :user "
                        .($table['where'] ? (' AND '.$table['where']) : '');
            }
            self::$timestampQuery = join(' UNION ', $statements);
        }
        return self::$timestampQuery;
    }

    /**
     * Get personal content (This has not to be cached anymore since we will
     * never display all users anymore)
     * 
     * @param md5 $user_id
     */
    public function getScoreContent() {

        // Fetch username
        $username = User::find($this->user_id)->username;

        // Get DB Connection
        $db = DBManager::get();

        // News
        $news = $db->fetchColumn("SELECT COUNT(*) FROM news_range WHERE range_id = ?", array($this->user_id));
        if ($news) {
            $tmp = sprintf(ngettext('Eine pers?nliche Ank¸ndigung', '%s pers?nliche Ank¸ndigungen', $news), $news);
            $content .= sprintf('<a href="%s">%s</a> ', URLHelper::getLink('dispatch.php/profile', compact('username')), Assets::img('icons/16/blue/news.png', tooltip2($tmp)));
        } else {
            $content .= Assets::img('blank.gif', array('width' => 16)) . ' ';
        }

        // Votes
        $vote = $db->fetchColumn("SELECT COUNT(*) FROM vote WHERE range_id = ?", array($this->user_id));
        if ($vote) {
            $tmp = sprintf(ngettext('Eine Umfrage', '%s Umfragen', $vote), $vote);
            $content .= sprintf('<a href="%s">%s</a> ', URLHelper::getLink('dispatch.php/profile', compact('username')), Assets::img('icons/16/blue/vote.png', tooltip2($tmp)));
        } else {
            $content .= Assets::img('blank.gif', array('width' => 16)) . ' ';
        }

        // Termine
        $termin = $db->fetchColumn("SELECT COUNT(*) FROM calendar_events WHERE range_id = ? AND class = 'PUBLIC'", array($this->user_id));
        if ($termin) {
            $tmp = sprintf(ngettext('Ein Termin', '%s Termine', $termin), $termin);
            $content .= sprintf('<a href="%s">%s</a> ', URLHelper::getLink('dispatch.php/profile#a', compact('username')), Assets::img('icons/16/blue/schedule.png', tooltip2($tmp)));
        } else {
            $content .= Assets::img('blank.gif', array('width' => 16)) . ' ';
        }

        // Literaturangaben
        $lit = $db->fetchColumn("SELECT COUNT(*) FROM lit_list WHERE range_id = ?", array($this->user_id));
        if ($lit) {
            $tmp = sprintf(ngettext('Eine Literaturangabe', '%s Literaturangaben', $lit), $lit);
            $content .= sprintf('<a href="%s">%s</a> ', URLHelper::getLink('dispatch.php/profile', compact('username')), Assets::img('icons/16/blue/literature.png', tooltip2($tmp)));
        } else {
            $content .= Assets::img('blank.gif', array('width' => 16)) . ' ';
        }
        return $content;
    }

}
