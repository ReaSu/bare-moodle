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
 * Renderer implementation for randomiser
 *
 * File         renderer.php
 * Encoding     UTF-8
 *
 * @package     local_randomiser
 *
 * @copyright   2019 Regula Sutter <sutter@technikum-wien.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once 'classes/pickusers.php';
require_once 'classes/dbhandler.php';

class local_randomiser_renderer extends \plugin_renderer_base {


    /**
     * return content for user picker page.
     */
    public function page_randomiser() {
        global $CFG;

        require_once($CFG->dirroot . '/local/randomiser/randomiser_form.php');
        require_once($CFG->dirroot . '/local/randomiser/lib.php');
        $course = $this->page->course;
        $context = $this->page->context;
        $lastlist = dbhandler::get_content($course->id);
        $students = null;
        $intro_string = get_string('randomiser_info', 'local_randomiser');

        $mform = new randomiser_form(new moodle_url($CFG->wwwroot . '/local/randomiser/randomiser.php'), array(
                'course' => $course,
                'context' => $context
        ));

        if ($data = $mform->get_data(false)) {
            $intro_string = get_string('randomiser_result', 'local_randomiser');
            $students = pickusers::pick_random_users($context, $data);
        } else if($lastlist) {
            $intro_string = get_string('last_list', 'local_randomiser');
            $students = $lastlist;
        }

        $help_string = get_string('pluginname', 'local_randomiser');

        $out = '';

        $out .= $this->header();
        $out .= $this->heading_with_help($help_string, 'randomiser', 'local_randomiser',
                'icon', $help_string);
        $out .= $this->box($intro_string, 'center');
        $out .= $this->format_result($students);
        $out .= $mform->render();
        $out .= $this->footer($course);
        return $out;
    }

    protected function format_result($result){
        if($result == null){
            $output = '';
        } else if (gettype($result[0]) == 'string') {
            $output = '<b>';
            $output .= get_string('no_students', 'local_randomiser');
            $output .= '</b>';
        } else {
            $output = '<ul style="padding: 30px 50px;">';
            foreach ($result as $student) {
                $output .= '<li>';
                $output .= $student['login'] . ' ' . $student['lastname'] . ' ' . $student['firstname'];
                $output .= '</li>';
            }
            $output .= '</ul>';
        }
        return $output;
    }

}