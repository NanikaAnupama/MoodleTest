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
 * Main page of local_helloworld: post a message and see all messages.
 *
 * @package    local_helloworld
 * @copyright  2026 Nandika
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$url = new moodle_url('/local/helloworld/index.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_helloworld'));
$PAGE->set_heading(get_string('pluginname', 'local_helloworld'));

$form = new \local_helloworld\form\message_form($url);

// Save a submitted message, then reload the page.
if ($data = $form->get_data()) {
    $record = new stdClass();
    $record->message = $data->message;
    $record->userid = $USER->id;
    $record->timecreated = time();
    //Moodle uses $DB as an abstraction layer. That means your plugin can use Moodle's database API instead of writing database-specific insertion code for MySQL or PostgreSQL.
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
        $items[] = get_string('messageline', 'local_helloworld', $a);
    }
    echo html_writer::alist($items);
}

echo $OUTPUT->footer();
