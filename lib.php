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
 * The enrolment class.
 *
 * @package   enrol_catalogue
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class
 *
 * @package   enrol_catalogue
 * @copyright 2019 - 2021 Mukudu Ltd - Bham UK
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_catalogue_plugin extends enrol_plugin {

    /**
     * This returns false for backwards compatibility, but it is really recommended.
     *
     * @return boolean
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * This returns false for backwards compatibility, but it is really recommended.
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        // We do not want more than 1 instance in a course.
        if (!enrol_is_enabled($this->get_name())) {
            return false;
        }

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/catalogue:config', $context)) {
            return false;
        }

        return true;
    }

    /**
     * Adds form elements to add/edit instance form.
     *
     * @param object $instance enrol instance or null if does not exist yet
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return void
     */
    public function edit_instance_form($instance, $mform, $context) {
        $nameattribs = array('size' => '20', 'maxlength' => '255');
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'server');
    }

    /**
     * Adds form elements to add/edit instance form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance data loaded from the DB.
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();
        if (core_text::strlen($data['name']) > 255) {
            $errors['name'] = get_string('err_maxlength', 'form', 255);
        }
        return $errors;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance( $instance->courseid);

        if (!has_capability('enrol/catalogue:config', $context)) {
            return false;
        }
        return true;
    }

    /**
     * Does this plugin assign protected roles are can they be manually removed?
     * @return bool - false means anybody may tweak roles, it does not use itemid and component when assigning roles
     */
    public function roles_protected() {
        return false;
    }

    /**
     * Does this plugin allow manual unenrolment of all users?
     * All plugins allowing this must implement 'enrol/xxx:unenrol' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol others freely, false means nobody can.
     */
    public function allow_unenrol($instance) {
        return true;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * All plugins allowing this must implement 'enrol/xxx:manage' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means it is possible to change enrol period and status in user_enrolments table
     */
    public function allow_manage($instance) {
        return true;
    }

}
