<?php

$services = array(
      'block_elo_reminder_users' => array(                      //the name of the web service
          'functions' => array ('block_elo_reminder_users_mailtouser','block_elo_reminder_users_viewhistories'), //web service functions of this service 
                                                                        //web service function name
          'requiredcapability' => '',                //if set, the web service user need this capability to access 
                                                     //any function of this service. For example: 'some/capability:specified'                 
          'restrictedusers' =>0,                      //if enabled, the Moodle administrator must link some user to this service
                                                      //into the administration
          'enabled'=>1,                               //if enabled, the service can be reachable on a default installation
                                                      //used only when installing the services
          'shortname'=>'eloreminderuserservice' //the short name used to refer to this service from elsewhere including when fetching a token
       )
  );

$functions = array(
    'block_elo_reminder_users_mailtouser' => array(
        'classname' => 'block_elo_reminder_users_external',
        'methodname' => 'mailtouser',
        'classpath' => 'blocks/elo_reminder_users/externallib.php',
        'description' => 'Sending a reminder to user',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ),
    'block_elo_reminder_users_viewhistories' => array(
        'classname' => 'block_elo_reminder_users_external',
        'methodname' => 'viewhistories',
        'classpath' => 'blocks/elo_reminder_users/externallib.php',
        'description' => 'View mail histories',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    )
);


