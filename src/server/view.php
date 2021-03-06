<?php
// This file is part of mod_elang for moodle.
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
 * Prints a particular instance of elang
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package     mod_elang
 *
 * @copyright   2013-2018 University of La Rochelle, France
 * @license     http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 *
 * @since       0.0.1
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');

// Get the moodle version.
$version = moodle_major_version(true);

// Get the course number.
$id = required_param('id', PARAM_INT);

// Get the optional view parameter ('player' for player view for teachers).
$view = optional_param('view', '', PARAM_ALPHA);

// Get the course module.
$cm = get_coursemodule_from_id('elang', $id, 0, false, MUST_EXIST);

// Get the course.
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Get the exercise.
$elang = $DB->get_record('elang', array('id' => $cm->instance), '*', MUST_EXIST);

// Verify the login.
require_login($course, true, $cm);

// Get the context.
$context = context_module::instance($cm->id);

if (has_capability('mod/elang:report', $context) && $view != 'player') {
    require_once(dirname(__FILE__) . '/report.php');
} else {
    // Update completion state.
    $completion = new completion_info($course);

    if ($completion->is_enabled($cm)) {
        $completion->set_module_viewed($cm);
    }

    require_once(dirname(__FILE__) . '/play.php');
}
