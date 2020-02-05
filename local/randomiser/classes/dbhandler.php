<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * reads and writes from/to the db
 *
 * File         dbhandler
 * Encoding     UTF-8
 *
 * @package     local_randomiser
 *
 * @copyright   2019 Regula Sutter <sutter@technikum-wien.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


class dbhandler {

    const TABLE = 'local_randomiser';

    public static function get_content($courseid){
        global $DB;
        $conditions = array('courseid'=>$courseid);
        if($DB->record_exists(self::TABLE, $conditions)) {
            $dbentry = $DB->get_records(self::TABLE, $conditions);
            return self::format_entries($dbentry);
        }
        return null;
    }

    protected static function format_entries($dbdata) {
        $user_objs = json_decode(array_values($dbdata)[0]->list);
        $user_arrays = array();
        foreach ($user_objs as $user_obj) {
            $user_arrays[] = (array)$user_obj;
        }
        return $user_arrays;
    }

    public static function persist($selection, $courseid){
        global $DB;
        $conditions = array('courseid'=>$courseid);
        $dataobject = self::create_obj($selection, $courseid);

        $dbid = $DB->get_field(self::TABLE, 'id', $conditions, $strictness=IGNORE_MISSING);
        if($dbid) {
            $dataobject->id = $dbid;
            $DB->update_record(self::TABLE, $dataobject, $bulk=false);
        } else {
            $DB->insert_record(self::TABLE, $dataobject, true, false);
        }
    }

    /**
     * @param $selection
     * @param $courseid
     * @return stdClass
     */
    protected static function create_obj($selection, $courseid) {
        $dataObject = new stdClass();
        $dataObject->courseid = $courseid;
        $dataObject->list = json_encode($selection);
        $dataObject->timestamp = time();
        return $dataObject;
    }
}