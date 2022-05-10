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
 * Contains the class for the My overview block.
 *
 * @package    block_myelostatistic
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * My overview block class.
 *
 * @package    block_myelostatistic
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myelostatistic extends block_base {

    /**
     * Init.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_myelostatistic');
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }
        global $GLOBAL_myelostatistic_setting;
        $eloestimateminute = 60;
        // Check if the tab to select wasn't passed in the URL, if so see if the user has any preference.
        if (!$tab = optional_param('myelostatistictab', null, PARAM_ALPHA)) {
            // Check if the user has no preference, if so get the site setting.
            if (!$tab = get_user_preferences('block_myelostatistic_last_tab')) {
                $config = get_config('block_myelostatistic');
                $tab = $config->defaulttab;
                $eloestimateminute = trim($config->estimatedonlineminute,'minute');
            }
        }
        $this->$eloestimateminute = $eloestimateminute;
        $renderable = new \block_myelostatistic\output\main($tab);
        $GLOBAL_myelostatistic_setting->eloestimateminute = $eloestimateminute;
        
        $renderer = $this->page->get_renderer('block_myelostatistic');

        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';
        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * This block does contain a configuration settings.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
}
