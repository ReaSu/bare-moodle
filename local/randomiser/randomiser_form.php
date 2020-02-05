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
 * Randomiser form
 *
 * File         randomiser_form.php
 * Encoding     UTF-8
 *
 * @package     local_randomiser
 *
 * @copyright   2019 Regula Sutter <sutter@technikum-wien.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


class randomiser_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {

        $mform = & $this->_form;
        $course = $this->_customdata['course'];

        $mform->addElement('header', 'general', ''); // Fill in the data depending on page params.

        // Number of students to pick
        $mform->addElement('text', 'amount', get_string('amount', 'local_randomiser'), array('size'=> 3));
        $mform->setType('amount', PARAM_INT);
        $mform->setDefault('amount', 5);
        $mform->addHelpButton('amount', 'amount', 'local_randomiser');
        $mform->addRule('amount', get_string('int_error', 'local_randomiser'), 'numeric', null, 'client');

        // select field to chose groups
        $groupnames = $this->get_group_names($course);
        if(count($groupnames) > 0) {
            $groupnames = self::sanitiseGroupNames($groupnames);

            $group_select = $mform->addElement('select', 'groups', get_string('groups', 'local_randomiser'), $groupnames);
            $group_select->setMultiple(true);
            $group_select->setSize(self::getSelectBoxSize(count($groupnames)));

            $mform->addHelpButton('groups', 'groups', 'local_randomiser');
            $mform->setDefault('groups', 0);
        }

        // Button: only submit, no cancel button
        $this->add_action_buttons(false, get_string('pick', 'local_randomiser'));


        $mform->addElement('hidden', 'id', $course->id);
        $mform->setType('id', PARAM_INT);
    }

    /**
     * @param $course
     * @return array of groups, sorted by their id
     */
    protected function get_group_names($course) {
        $group_names = array();
        $groups = groups_get_all_groups($course->id);

        foreach ($groups as $group) {
            $group_names[$group->id] = $group->name;
        }
        return $group_names;
    }

    function sanitiseGroupNames($groupNames) {
        $groupNames[0] = get_string('all', 'local_randomiser');
        ksort($groupNames, SORT_NUMERIC);
        return $groupNames;
    }

    /**
     * returns the size of the select field
     * to fit number of groups, or max 5.
     *
     * @param $count - the number of options
     * @return int size of the select field
     */
    function getSelectBoxSize($count) {
        if($count > 5) {
            return 5;
        } else {
            return $count;
        }
    }
}
