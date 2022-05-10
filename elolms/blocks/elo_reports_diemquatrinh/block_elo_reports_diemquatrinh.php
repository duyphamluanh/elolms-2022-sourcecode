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
 * Elo Reminder courses block.
 *
 * @package    block_elo_reports_log_bbb_log_bbb
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * This block needs to be reworked.
 * The new roles system does away with the concepts of rigid student and
 * teacher roles.
 */
class block_elo_reports_diemquatrinh extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_elo_reports_diemquatrinh');
        $this->title = '<span id="viewber">' . $this->title . '</span>';
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }
        $context = context_block::instance($this->instance->id);
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }
        $icon = $OUTPUT->pix_icon('i/configlock', get_string('course'));
        if (!has_capability('block/elo_reports_diemquatrinh:viewindashboard', $context)){
            return $this->content;
        }
        $this->content->footer = $icon . "<a href=\"$CFG->wwwroot/blocks/elo_reports_diemquatrinh/view.php\">" . get_string("tocontinue", 'block_elo_reports_diemquatrinh') . "</a> ...";

        return $this->content;
    }

}
