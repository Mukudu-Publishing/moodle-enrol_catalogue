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
 * Plugin lib file
 *
 * @package    enrol_catalogue
 * @copyright  2021 Mukudu Publishing
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The format for the class name is important. //
class enrol_catalogue_plugin extends enrol_plugin {
    
    // ** Since Moodle 3.1, this function is now the recommended way for enrol plugins to define their add/edit interfaces ** //
    public function use_standard_editing_ui() {
        return true;
    }
    
    public function can_add_instance($courseid) {
        
        $context = context_course::instance($courseid, MUST_EXIST);
        
        // Check if plugin is enabled.
        if (!enrol_is_enabled($this->get_name())) {
            return false;
        }
        
        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/catalogue:config', $context)) {
            return false;
        }
        
        return true;
    }
    
    public function edit_instance_form($instance, $mform, $context) {
        $nameattribs = array('size' => '20', 'maxlength' => '255');
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $mform->setType('name', PARAM_TEXT);
    }
    
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();
        if (core_text::strlen($data['name']) > 255) {
            $errors['name'] = get_string('err_maxlength', 'form', 255);
        }
        return $errors;
    }
    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        
        if (!has_capability('enrol/catalogue:config', $context)) {
            return false;
        }
        return true;
    }
    
    public function roles_protected() {
        return false;
    }
    
    public function allow_unenrol($instance) {
        return true;
    }
    
    public function allow_manage($instance) {
        return true;
    }
}
