<?php
// [File header: GPL comment + PHPDoc with @package local_helloworld]

// 1. Load Moodle.
//    Your file is at public/local/helloworld/index.php.
//    Moodle's config.php is at public/config.php.
//    → require_once + __DIR__ + the right number of '/..'

// 2. Only logged-in users may see this page.
//    → require_login()

// 3. Tell Moodle about this page.
//    → $PAGE->set_url(...)       with a new moodle_url('/local/helloworld/index.php')
//    → $PAGE->set_context(...)   the system context: context_system::instance()
//    → $PAGE->set_title(...)     text for the browser tab   (use get_string!)
//    → $PAGE->set_heading(...)   big heading on the page    (use get_string!)

// 4. Start the page.
//    → echo $OUTPUT->header();

// 5. Your content: the greeting, using the current user's first name.
//    → get_string('greeting', 'local_helloworld', <the name>)
//    → the logged-in user is in the global $USER (look at its 'firstname' property)
//    → wrap the user's name in s() before using it (it escapes HTML, for safety)
//    → output it, e.g. with $OUTPUT->heading(...) or html_writer::tag('p', ...)

// 6. End the page.
//    → echo $OUTPUT->footer();


 require_once(__DIR__ . '/../../config.php');

require_login();

$url = new moodle_url('/local/helloworld/index.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_helloworld'));
$PAGE->set_heading(get_string('pluginname', 'local_helloworld'));

$form = new \local_helloworld\form\message_form($url);

echo $OUTPUT->header();

$greeting = get_string('greeting', 'local_helloworld', s($USER->firstname));
echo $OUTPUT->heading($greeting);

if ($data = $form->get_data()) {
    $message = get_string('yousaid', 'local_helloworld', s($data->message));
    echo $OUTPUT->notification($message, \core\output\notification::NOTIFY_SUCCESS);
}

$form->display();

echo $OUTPUT->footer();


