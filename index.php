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
 * Main page of local_helloworld: post, list and delete messages.
 *
 * @package    local_helloworld
 * @copyright  2026 Nandika
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

// the delete icon and the confirm icon will have a optional uurl parameter  like index.php?delete=2
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$url = new moodle_url('/local/helloworld/index.php');
$context = context_system::instance();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_helloworld'));
$PAGE->set_heading(get_string('pluginname', 'local_helloworld'));

$candelete = has_capability('local/helloworld:deletemessage', $context);

// Delete a message: first ask "are you sure?", then delete after confirmation.
if ($delete) {
    require_capability('local/helloworld:deletemessage', $context);
    $todelete = $DB->get_record('local_helloworld_messages', ['id' => $delete], '*', MUST_EXIST);

    if ($confirm) {
        require_sesskey();
        $DB->delete_records('local_helloworld_messages', ['id' => $todelete->id]);
        redirect($url, get_string('messagedeleted', 'local_helloworld'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    $confirmurl = new moodle_url($url, ['delete' => $todelete->id, 'confirm' => 1, 'sesskey' => sesskey()]);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('confirmdelete', 'local_helloworld', s($todelete->message)),
        $confirmurl,
        $url
    );
    echo $OUTPUT->footer();
    exit;
}

$form = new \local_helloworld\form\message_form($url);

// Save a submitted message, then reload the page.
if ($data = $form->get_data()) {
    $record = new stdClass();
    $record->message = $data->message;
    $record->userid = $USER->id;
    $record->timecreated = time();
    $DB->insert_record('local_helloworld_messages', $record);

    redirect($url, get_string('messagesaved', 'local_helloworld'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

// Load every message, newest first, together with the poster's first name.
$sql = "SELECT m.id, m.message, m.timecreated, u.firstname
          FROM {local_helloworld_messages} m
          JOIN {user} u ON u.id = m.userid
      ORDER BY m.timecreated DESC";
$messages = $DB->get_records_sql($sql);

echo $OUTPUT->header();

$greeting = get_string('greeting', 'local_helloworld', s($USER->firstname));
echo $OUTPUT->heading($greeting);

$form->display();

echo $OUTPUT->heading(get_string('allmessages', 'local_helloworld'), 3);

if (empty($messages)) {
    echo html_writer::tag('p', get_string('nomessages', 'local_helloworld'));
} else {
    $items = [];
    foreach ($messages as $message) {
        $a = new stdClass();
        $a->name = s($message->firstname);
        $a->time = userdate($message->timecreated);
        $a->message = s($message->message);
        $line = get_string('messageline', 'local_helloworld', $a);

        if ($candelete) {
            $deleteurl = new moodle_url($url, ['delete' => $message->id]);
            $line .= ' ' . $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete', get_string('delete')));
        }

        $items[] = $line;
    }
    echo html_writer::alist($items);
}

echo $OUTPUT->footer();
