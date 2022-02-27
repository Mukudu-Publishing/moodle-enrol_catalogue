<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Index file
 *
 * @package   enrol_catalogue
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login(); // Ensure the user is authenticated.
global $DB, $USER, $PAGE, $OUTPUT;

$errmsg = '';
if (optional_param('action', '', PARAM_TEXT) == 'enrolme') {
    
    // Get course id - we require this parameter.
    $courseid = required_param('courseid', PARAM_INT);
    $coursecontext = context_course::instance($courseid, MUST_EXIST);
    
    // Find the student role id.
    $studentroleid = 0;
    $courseroles = get_all_roles($coursecontext);
    foreach ($courseroles as $courserole) {
        if ($courserole->archetype == 'student') {
            $studentroleid = $courserole->id;
            break;
        }
    }
    if ($studentroleid) {
        
        // Get enrol plugin instance in course - could have used enrol_get_instances().
        $enrolinstances = enrol_get_instances($courseid, true);
        foreach ($enrolinstances as $instance) {
            if ($instance->enrol == 'catalogue') {
                break;
            }
        }
        
        // Do the enrolment here.
        $enrolplugin = enrol_get_plugin('catalogue');
        $enrolplugin->enrol_user($instance, $USER->id, $studentroleid);
        // Check- enrol user does not return results.
        if (is_enrolled($coursecontext)) {
            // Redirect to then newly enrolled course.
            $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
            redirect($courseurl);
        } else {
            $errmsg = get_string('failedenrol', 'enrol_catalogue');
        }
    } else {  // This should not happen - paranoid check.
        $errmsg = get_string('nostudentrole', 'enrol_catalogue');
    }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/enrol/catalogue/index.php'));
$PAGE->set_heading(get_string('catalogheader1', 'enrol_catalogue'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'enrol_catalogue') . ': (' . get_config('enrol_catalogue', 'version') .')');

// Generate the page.
echo $OUTPUT->header();

// Display error if one has been created.
if ($errmsg) {
    echo $OUTPUT->notification($errmsg, 'error');
}

// Is our plugin enabled?
$enabledplugins= enrol_get_plugins(true);  // From the Enrolment API

if (in_array('catalogue', array_keys($enabledplugins))) {
    
    // Get all enabled instances of the plugins - NO API functionality to do this.
    $instances = $DB->get_records('enrol', array('enrol' => 'catalogue', 'status' => ENROL_INSTANCE_ENABLED), 'courseid');
    
    if (empty($instances)) {
        // No courses in the catalogue.
        echo html_writer::tag('h3', get_string('nocoursesincat', 'enrol_catalogue'));
    } else {
        // Set up the enrolment request for later.
        $enrolurl = $PAGE->url;
        $enrolurl->param('action', 'enrolme');
        
        // Get all the user's course enrolments.
        $myenrolments = enrol_get_all_users_courses($USER->id);
        
        $catcourses = new html_table(); // Html table.
        foreach ($instances as $instance) {
            // Is the user enrolled already?
            if (empty($myenrolments[$instance->courseid])) {
                // Get the course details for display.
                $course = get_course($instance->courseid);
                // Make sure the course is available.
                if ($course->visible) {
                    $enrolurl->param('courseid', $course->id);
                    $enrolbutton = new single_button($enrolurl, get_string('enrolinvite', 'enrol_catalogue'));
                    $catcourses->data[] =
                        new html_table_row(array(
                            new html_table_cell($course->fullname),
                            new html_table_cell($course->summary),
                            new html_table_cell($OUTPUT->render($enrolbutton))
                        )
                    );
                }
            }
        }
        
        if (count($catcourses->data)) { // Any data?
            echo html_writer::tag('h3', get_string('catalogheader2', 'enrol_catalogue'));
            echo html_writer::table($catcourses);
        } else {
            // No data - inform the user about it.
            echo html_writer::tag('h3', get_string('nocoursesincat', 'enrol_catalogue'));
        }
    }
} else {
    // Our plugin not enabled - inform the user.
    echo html_writer::tag('h3', get_string('plugindisabled', 'enrol_catalogue'));
}

echo $OUTPUT->footer();
