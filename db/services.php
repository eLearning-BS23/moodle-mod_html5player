<?php


defined('MOODLE_INTERNAL') || die;

$functions = array(
    'mod_html5player_set_module_progress' => array(
        'classname'     => 'mod_html5player_external',
        'methodname'    => 'html5player_set_progress',
        'description'   => 'Update html5player progress',
        'type'          => 'write',
        'capabilities'  => 'mod/html5player:view',
        'ajax'      => true,
    )
);