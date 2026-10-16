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

namespace local_helloworld\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_helloworld: describes, exports and deletes users' messages.
 *
 * @package    local_helloworld
 * @copyright  2026 Nandika
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes the personal data this plugin stores.
     *
     * @param collection $collection The collection to add the description to.
     * @return collection The same collection, with this plugin's data added.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_helloworld_messages',
            [
                'message' => 'privacy:metadata:messages:message',
                'userid' => 'privacy:metadata:messages:userid',
                'timecreated' => 'privacy:metadata:messages:timecreated',
            ],
            'privacy:metadata:messages'
        );

        return $collection;
    }

    /**
     * Returns the contexts where the given user has data.
     *
     * @param int $userid The user to look for.
     * @return contextlist The contexts holding data for that user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();

        // All messages live in the system context, so it is either that or nothing.
        if ($DB->record_exists('local_helloworld_messages', ['userid' => $userid])) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Adds every user who has data in the given context to the list.
     *
     * @param userlist $userlist The list to add users to; it also says which context is being asked about.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $sql = "SELECT DISTINCT userid
                  FROM {local_helloworld_messages}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Exports the messages of the user in the approved contexts.
     *
     * @param approved_contextlist $contextlist The user and the contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            $records = $DB->get_records('local_helloworld_messages', ['userid' => $userid], 'timecreated ASC, id ASC');
            if (empty($records)) {
                continue;
            }

            $messages = [];
            foreach ($records as $record) {
                $messages[] = [
                    'message' => $record->message,
                    'timecreated' => transform::datetime($record->timecreated),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_helloworld')],
                (object) ['messages' => $messages]
            );
        }
    }

    /**
     * Deletes everyone's messages in the given context.
     *
     * @param \context $context The context to delete from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('local_helloworld_messages');
        }
    }

    /**
     * Deletes one user's messages in the approved contexts.
     *
     * @param approved_contextlist $contextlist The user and the contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                $DB->delete_records('local_helloworld_messages', ['userid' => $userid]);
            }
        }
    }

    /**
     * Deletes the messages of several users in one context.
     *
     * @param approved_userlist $userlist The context and the users approved for deletion.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_helloworld_messages', "userid $insql", $params);
    }
}

