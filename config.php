<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'vip';
$CFG->dbname    = 'elolms';
$CFG->dbuser    = 'elo';
$CFG->dbpass    = 'ELO@2022';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'https://elolms.edu.vn';
$CFG->dataroot  = '/mnt/oudata/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;
//$CFG->localcachedir = '/var/run/moodle/localcache';
$CFG->cachedir = '/mnt/oudata/moodledata/cachedir';
$CFG->tempdir = '/mnt/oudata/moodledata/tempdir';

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
