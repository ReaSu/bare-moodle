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
 * picks the requested number of students from given group
 *
 * File         pickusersphp
 * Encoding     UTF-8
 *
 * @package     local_randomiser
 *
 * @copyright   2019 Regula Sutter <sutter@technikum-wien.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/group/lib.php';
require_once $CFG->dirroot.'/lib/enrollib.php';


class pickusers {

    /**
     * @param $context
     * @param $data
     * @return array|null
     */
    static function pick_random_users($context, $data) {

        $students = self::get_students($context, $data);
        $amount = self::get_amount($data, count($students));
        $selection = self::get_selection($amount, $students);
        // I only want to persist a selection if it actually yielded a meaningful result.
        if(gettype($selection[0]) == 'array') {
            dbhandler::persist($selection, $data->id);
        }
        return $selection;
    }

    /**
     * gets all students that are members of selected groups.
     * If no groups were selected, or 'all' was selected, get all enrolled students
     * @param $context
     * @param $data array of data from the form
     * @return array of user arrays
     */
    protected static function get_students($context, $data) {
        $fields = 'u.lastname AS lastname, u.firstname AS firstname, u.username AS login, u.id AS userid';

        if (array_key_exists('groups', $data) && $data->groups[0] != '0') {
            $all_members = self::get_userdata_from_groups($data->groups, $fields);
        } else {
            $all_members = self::get_userdata_from_course($context, $fields);
        }

        $userdata = self::extract_relevant_data($all_members);
        $unique_users = self::eliminate_duplicates($userdata);
        $students = self::check_permissions($context, $unique_users);

        return $students;
    }


    /**
     * gets names of students if there are no groups in the course or if "All" was selected in a course with groups.
     * Therefore the result may vary depending on whether all groups are selected, or the item "all" is selected.
     * @param $context
     * @param $fields string to select in DB
     * @return array of arrays of user objects
     */
    protected static function get_userdata_from_course($context, $fields){
        $results[] = get_enrolled_users($context, '', 0, $fields, null, 0, 0, true);
        return $results;
    }

    /**
     * gets unique names of students if one or more groups were selected.
     * activity status of students is not checked.
     * @param $groups
     * @param $fields string to select from DB
     * @return array of arrays of user objects
     */
    protected static function get_userdata_from_groups($groups, $fields) {
        $groupmembers = array();
        foreach ($groups as $key=>$value){
            $groupmembers[] = groups_get_members(intval($value), $fields);
        }
        return $groupmembers;
    }

    /**
     * gets the needed data out of the results and puts it in the correct form
     * @param $results
     * @return array of arrays with key => value paris for each student
     */
    static function extract_relevant_data($results) {
        $usernames = array();
        foreach ($results as $key => $value) {
            foreach ($value as $user) {
                $usernames[] = ['login' => $user->login,
                                'lastname' => $user->lastname,
                                'firstname' => $user->firstname,
                                'userid' => $user->userid];
            }
        }
        return $usernames;
    }

    /**
     * reads the array of student arrays and filters out all multiple entries
     * users can be in the list more then once if they are a member of more than one selected group.
     * @param $students: array of student arrays
     * @return array of student arrays, without duplicates
     */
    protected static function eliminate_duplicates($students){
        $uids = array();
        $unique_students = array();
        foreach ($students as $student) {
            $uid = $student['userid'];
            $searchresult = !in_array($uid, $uids);
            if($searchresult) {
                $uids[] = $uid;
                $unique_students[] = $student;
            }
        }

        return $unique_students;
    }

    /**
     * @param $context
     * @param $users
     * @return array
     */
    static function check_permissions($context, $users) {
        $students = array();
        foreach ($users as $user) {
            if(has_capability("mod/assign:submit", $context, $user['userid'])) {
                $students[] = $user;
            }
        }
        return $students;
    }

    /**
     * checks whether an amount has been supplied, otherwise uses a standard of 5.
     * @param $data stdClass that is returned from the form
     * @param $usercount int number of students in selected groups / course
     * @return int -- the number of students that will be selected (no more than
     *   the number of students enrolled in the course)
     */
    protected static function get_amount($data, $usercount) {
        if($data->amount || $data->amount == '0') {
            $amount = $data->amount;
        } else {
            $amount = 5;
        }
        return min($amount, $usercount);
    }

    /**
     * @param $amount int the number of students that should be selected.
     * @param $students array of student arrays to select from
     * @return array of selected users, or an error message if there are no users to select from.
     */
    protected static function get_selection($amount, $students) {
        $selected = array();
        if(count($students) == 0){
            // this is a hack so I can differentiate between an empty array due to no prior searches
            // and an empty array because there are no students in the pool.
            // I welcome other ideas on how to deal with this.
            $selected[] = 'no students';
        } else {
            do {
                try {
                    $random_number = random_int(0, count($students) - 1);
                    $selected[] = $students[$random_number];
                    // remove the selected student from the pool to avoid more duplicates
                    array_splice($students, $random_number, 1);
                } catch (Throwable $e) {
                    // Ignore. The error is thrown when $students doesn't contain any values.
                    // we already took care of that.
                }
            } while (count($selected) < $amount);
        }
        return $selected;
    }
}