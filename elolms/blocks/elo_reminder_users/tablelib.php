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
 * @package    core
 * @subpackage lib
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class elo_flexible_table extends flexible_table {
    /**
     * @param string $uniqueid a string identifying this table.Used as a key in
     *                          session  vars.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);
    }

    /**
     * This method actually directly echoes the row passed to it now or adds it
     * to the download. If this is the first row and start_output has not
     * already been called this method also calls start_output to open the table
     * or send headers for the downloaded.
     * Can be used as before. print_html now calls finish_html to close table.
     *
     * @param array $row a numerically keyed row of data to add to the table.
     * @param string $classname CSS class name to add to this row's tr tag.
     * @return bool success.
     */
    function elo_add_data($row, $classname = '') {
        $html = '';
        if (!$this->setup) {
            $html = '';
        }
        if (!$this->started_output) {
            $html .= $this->elo_start_output();
        }
        if ($this->exportclass!==null) {
            if ($row === null) {
                $html .= $this->exportclass->add_seperator();
            } else {
                $html .= $this->exportclass->add_data($row);
            }
        } else {
            $html .= $this->elo_print_row($row, $classname);
        }
        return $html;
    }

    /**
     * You should call this to finish outputting the table data after adding
     * data to the table with add_data or add_data_keyed.
     *
     */
    function elo_finish_output($closeexportclassdoc = true) {
        $html = '';
        if ($this->exportclass!==null) {
            $this->exportclass->finish_table();
            if ($closeexportclassdoc) {
                $this->exportclass->finish_document();
            }
        } else {
            $html .= $this->elo_finish_html();
        }
        return $html;
    }

    /**
     * This function is not part of the public api.
     * You don't normally need to call this. It is called automatically when
     * needed when you start adding data to the table.
     *
     */
    function elo_start_output() {
        $html = '';
        $this->started_output = true;
        if ($this->exportclass!==null) {
            $this->exportclass->start_table($this->sheettitle);
            $html .=$this->exportclass->output_headers($this->headers);
        } else {
            $html .=$this->elo_start_html();
            $html .=$this->elo_print_headers();
            $html .= html_writer::start_tag('tbody');
        }
        return $html;
    }

    /**
     * This function is not part of the public api.
     */
    function elo_print_row($row, $classname = '') {
        return $this->get_row_html($row, $classname);
    }


    /**
     * This function is not part of the public api.
     */
    function elo_finish_html() {
        global $OUTPUT;

        $html = '';

        if (!$this->started_output) {
            //no data has been added to the table.
            $this->print_nothing_to_display();

        } else {
            // Print empty rows to fill the table to the current pagesize.
            // This is done so the header aria-controls attributes do not point to
            // non existant elements.
            $emptyrow = array_fill(0, count($this->columns), '');
            while ($this->currentrow < $this->pagesize) {
                $this->print_row($emptyrow, 'emptyrow');
            }

            $html .= html_writer::end_tag('tbody');
            $html .= html_writer::end_tag('table');
            $html .= html_writer::end_tag('div');
            // $this->wrap_html_finish();

            // Paging bar
            if(in_array(TABLE_P_BOTTOM, $this->showdownloadbuttonsat)) {
                $html .= $this->download_buttons();
            }

            if($this->use_pages) {
                $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
                $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
                $html .= $OUTPUT->render($pagingbar);
            }
        }
        return $html;
    }

    /**
     * This function is not part of the public api.
     */
    function elo_print_headers() {
        global $CFG, $OUTPUT, $PAGE;

        $html = '';

        $html .= html_writer::start_tag('thead');
        $html .= html_writer::start_tag('tr');
        foreach ($this->columns as $column => $index) {

            $icon_hide = '';
            if ($this->is_collapsible) {
                $icon_hide = $this->show_hide_link($column, $index);
            }

            $primarysortcolumn = '';
            $primarysortorder  = '';
            if (reset($this->prefs['sortby'])) {
                $primarysortcolumn = key($this->prefs['sortby']);
                $primarysortorder  = current($this->prefs['sortby']);
            }

            switch ($column) {

                case 'fullname':
                    // Check the full name display for sortable fields.
                    if (has_capability('moodle/site:viewfullnames', $PAGE->context)) {
                        $nameformat = $CFG->alternativefullnameformat;
                    } else {
                        $nameformat = $CFG->fullnamedisplay;
                    }

                    if ($nameformat == 'language') {
                        $nameformat = get_string('fullnamedisplay');
                    }

                    $requirednames = order_in_string(get_all_user_name_fields(), $nameformat);

                    if (!empty($requirednames)) {
                        if ($this->is_sortable($column)) {
                            // Done this way for the possibility of more than two sortable full name display fields.
                            $this->headers[$index] = '';
                            foreach ($requirednames as $name) {
                                $sortname = $this->sort_link(get_string($name),
                                        $name, $primarysortcolumn === $name, $primarysortorder);
                                $this->headers[$index] .= $sortname . ' / ';
                            }
                            $helpicon = '';
                            if (isset($this->helpforheaders[$index])) {
                                $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                            }
                            $this->headers[$index] = substr($this->headers[$index], 0, -3). $helpicon;
                        }
                    }
                break;

                case 'userpic':
                    // do nothing, do not display sortable links
                break;

                default:
                    if ($this->is_sortable($column)) {
                        $helpicon = '';
                        if (isset($this->helpforheaders[$index])) {
                            $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                        }
                        $this->headers[$index] = $this->sort_link($this->headers[$index],
                                $column, $primarysortcolumn == $column, $primarysortorder) . $helpicon;
                    }
            }

            $attributes = array(
                'class' => 'header c' . $index . $this->column_class[$column],
                'scope' => 'col',
            );
            if ($this->headers[$index] === NULL) {
                $content = '&nbsp;';
            } else if (!empty($this->prefs['collapse'][$column])) {
                $content = $icon_hide;
            } else {
                if (is_array($this->column_style[$column])) {
                    $attributes['style'] = $this->make_styles_string($this->column_style[$column]);
                }
                $helpicon = '';
                if (isset($this->helpforheaders[$index]) && !$this->is_sortable($column)) {
                    $helpicon  = $OUTPUT->render($this->helpforheaders[$index]);
                }
                $content = $this->headers[$index] . $helpicon . html_writer::tag('div',
                        $icon_hide, array('class' => 'commands'));
            }
            $html .= html_writer::tag('th', $content, $attributes);
        }

        $html .= html_writer::end_tag('tr');
        $html .= html_writer::end_tag('thead');

        return $html;
    }

    /**
     * This function is not part of the public api.
     */
    function elo_print_initials_bar() {
        global $OUTPUT;

        $html = '';

        $ifirst = $this->get_initial_first();
        $ilast = $this->get_initial_last();
        if (is_null($ifirst)) {
            $ifirst = '';
        }
        if (is_null($ilast)) {
            $ilast = '';
        }

        if ((!empty($ifirst) || !empty($ilast) ||$this->use_initials)
                && isset($this->columns['fullname'])) {
            $prefixfirst = $this->request[TABLE_VAR_IFIRST];
            $prefixlast = $this->request[TABLE_VAR_ILAST];
            $html .= $OUTPUT->initials_bar($ifirst, 'firstinitial', get_string('firstname'), $prefixfirst, $this->baseurl);
            $html .= $OUTPUT->initials_bar($ilast, 'lastinitial', get_string('lastname'), $prefixlast, $this->baseurl);
        }
        return $html;

    }

    /**
     * This function is not part of the public api.
     */
    function elo_print_nothing_to_display() {
        global $OUTPUT;
        $html = '';

        // Render button to allow user to reset table preferences.
        // $html .= $this->render_reset_button();

        $html .= $this->elo_print_initials_bar();

        $html .= $OUTPUT->heading(get_string('nothingtodisplay'));

        return $html;
    }

    /**
     * This function is not part of the public api.
     */
    function elo_start_html() {
        global $OUTPUT;

        $html = '';

        // Render button to allow user to reset table preferences.
        // $html .= $this->render_reset_button();

        // Do we need to print initial bars?
        $html .= $this->elo_print_initials_bar();

        // Paging bar
        if ($this->use_pages) {
            $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
            $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
            $html .= $OUTPUT->render($pagingbar);
        }

        if (in_array(TABLE_P_TOP, $this->showdownloadbuttonsat)) {
            $html .= $this->download_buttons();
        }

        // $this->wrap_html_start();
        // Start of main data table

        $html .= html_writer::start_tag('div', array('class' => 'no-overflow'));
        $html .= html_writer::start_tag('table', $this->attributes);

        return $html;
    }
}