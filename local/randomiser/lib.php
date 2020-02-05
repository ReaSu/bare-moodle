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
 * Code to add Randomiser plugin to users menu
 *
 * File         lib.php
 * Encoding     UTF-8
 *
 * @package     local_randomiser
 *
 * @copyright   2019 Regula Sutter <sutter@technikum-wien.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function local_randomiser_extend_settings_navigation(settings_navigation $navigation, $context) {
    global $CFG;

    // If not in a course context, then leave.
    if ($context == null || $context->contextlevel != CONTEXT_COURSE) {
        return;
    }
    // Front page has a 'frontpagesettings' node, other courses will have 'courseadmin' node.
    if (null == ($courseadminnode = $navigation->get('courseadmin'))) {
        // Keeps us off the front page.
        return;
    }
    if (null == ($useradminnode = $courseadminnode->get('users'))) {
        return;
    }

    if (has_capability('mod/assign:grade', $context)) {
        $url = new moodle_url($CFG->wwwroot . '/local/randomiser/randomiser.php',
                array('id' => $context->instanceid));
        $useradminnode->add(get_string('pluginname', 'local_randomiser'), $url,
                navigation_node::TYPE_SETTING, null, 'randomuserpicker', new pix_icon('pix/nounDice', 'ranomiser icon'));
    }
}