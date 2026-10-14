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

namespace local_helloworld\form;

defined('MOODLE_INTERNAL') || die();
// loads the gobal libraries and the formslib.php file, which contains the definition of the moodleform class.
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Form where a user types a message.
 *
 * @package    local_helloworld
 * @copyright  2026 Nandika
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// we are basically extending the moodleform class to create our own form.
class message_form extends \moodleform {

    /**
     * Defines the fields of the form.
     */
    public function definition() {
        //stores the underlying form object in a variable for easier access.
        $mform = $this->_form;

        // Add a text field for the message.
        $mform->addElement('text', 'message', get_string('yourmessage', 'local_helloworld'));
        //This tells Moodle that the message field should be processed as text.
        $mform->setType('message', PARAM_TEXT);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        //theser add_action_buttons are classes, that was taken from the moodle form class, false means do not add a cancel button
        $this->add_action_buttons(false, get_string('submit'));
    }
}
