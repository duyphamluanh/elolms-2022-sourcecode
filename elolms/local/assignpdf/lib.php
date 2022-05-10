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
 *
 * @package    block_elo_support_lib
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function custom_htmllize_render(&$dir,$contextid) {
    global $CFG;
    if (empty($dir['subdirs']) and empty($dir['files'])) {
        return '';
    }
    $render = '';
    foreach ($dir['files'] as $file) {
        $filename = $file->get_filename();
        $path = '/' .
                $contextid .
                '/' .
                'assignsubmission_file' .
                '/' .
                'submission_files' .
                '/' .
                $file->get_itemid() .
                $file->get_filepath() .
                $filename;
        $url = file_encode_url("$CFG->wwwroot/pluginfile.php", $path, false);
        $path_parts = pathinfo($filename);
        if (in_array($path_parts['extension'], array('mp4', 'avi', 'webm', 'mpeg-2'))) {
            $render .= '<div><video style="width: 80%; height: auto; max-width: 640px; max-height: 480px; display: flex; margin: auto; padding: 10px " onloadstart="this.volume=0.25" controls="" ><source src="' . $url . '" type="video/mp4">Your browser does not support HTML video.</video></div>';
            continue;
        }
        if (in_array($path_parts['extension'], array('ogg', 'mp3', 'wav', 'mpeg'))) {
            $render .= '<div><audio style="width: 80%; height: auto;min-height: 50px; max-width: 640px; max-height: 480px; display: flex; margin: auto; padding: 10px " onloadstart="this.volume=0.25" controls="" ><source src="' . $url . '" type="audio/mp3"></video>Your browser does not support the audio element.</audio>';
        }
    }
    return $render;
}
