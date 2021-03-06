<?php

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

// Assets
$app['stylesheets'] = [
    'agendav.css',
    'jquery-ui.css',
    'jquery-ui.structure.css',
    'jquery-ui.theme.css',
    'fullcalendar.css',
    'jquery.qtip.css',
    'freeow.css',
    'jquery.timepicker.css',
    'colorpicker.css',
];

$app['print.stylesheets'] = [
    'app.print.css',
    'fullcalendar.print.css',
];

$app['scripts'] = [
    'libs/jquery.js',
    'libs/moment.js',
    'libs/moment-timezone-with-data-2010-2020.min.js',
    'libs/button.js',
    'libs/jquery-ui.js',
    'libs/tab.js',
    'libs/jquery.timepicker.js',
    'libs/jquery.freeow.min.js',
    'libs/jquery.colorPicker.js',
    'libs/imagesloaded.pkg.min.js',
    'libs/jquery.qtip.js',
    'libs/jquery.colorhelpers.js',
    'libs/jquery.serializeobject.js',
    'libs/fullcalendar.js',
    'libs/rrule.js',
    'libs/nlp.js',
    'templates/dust-core.js',
    'templates/dust-helpers.js',
    'templates/templates.js',
    'datetime.js',
    'repeat-form.js',
    'app.js',
];

// Session parameters
$app['session.storage.options'] = [
    'name' => 'agendav_sess',
    'cookie_lifetime' => 0,
];

// Calendar colors
$app['calendar.colors'] = [
    'D4EAEF',
    '3A89C9',
    '107FC9',
    'FAC5C0',
    'FF4E50',
    'BD3737',
    'C9DF8A',
    '77AB59',
    '36802D',
    'F8F087',
    'E6D5C1',
    '3E4147'
];

// Configure logging (1/2)
$app['monolog.level'] = 'WARNING';

// Languages
$app['languages'] = require __DIR__ . '/languages.php';

// Fullcalendar language packs
$app['fullcalendar.languages'] = [
    //'br' => 'en', // Missing
    'ca' => 'ca',
    'de_DE' => 'de',
    //'et' => 'en', // Missing
    //'en' => 'en',
    'es_ES' => 'es',
    'fr_FR' => 'fr',
    'hr_HR' => 'hr',
    'it_IT' => 'it',
    'nl_NL' => 'nl',
    //'nb' => 'en', // Missing
    'pl' => 'pl',
    'pt_BR' => 'pt-br',
    'fi' => 'fi',
    'sv_SE' => 'sv',
    'ru_RU' => 'ru',
];


/**
 * Site configuration
 *
 * IMPORTANT: These are AgenDAV defaults. Do not change this file, add your
 * changes to settings.php
 */
// Site title
$app['site.title'] = 'Our calendar';

// Site logo (should be placed in public/img). Optional
$app['site.logo'] = 'agendav_100transp.png';

// Site footer. Optional
$app['site.footer'] = 'AgenDAV ' . \AgenDAV\Version::V;

// Trusted proxy ips
$app['proxies'] = [];

// Database settings
$app['db.options'] = [
        'dbname' => 'agendav',
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_mysql'
];

// Encryption key
$app['encryption.key'] = 'abcdefg';

// CSRF secret
$app['csrf.secret'] = 'lkjihgfedcba';

// Log path
$app['log.path'] = '/var/log/agendav/';

// Base URL
$app['caldav.baseurl'] = 'http://localhost:81/';

// Authentication method required by CalDAV server (basic or digest)
$app['caldav.authmethod'] = 'basic';

// Whether to show public CalDAV urls
$app['caldav.publicurls'] = true;

// Whether to show public CalDAV urls
$app['caldav.baseurl.public'] = 'https://caldav.server.com';

// Calendar sharing
$app['calendar.sharing'] = false;

// Default timezone
$app['defaults.timezone'] = 'Europe/Madrid';

// Default languajge
$app['defaults.language'] = 'en';

// Default time format. Options: '12' / '24'
$app['defaults.time_format'] = '24';

/*
 * Default date format. Options:
 *
 * - ymd: YYYY-mm-dd
 * - dmy: dd-mm-YYYY
 * - mdy: mm-dd-YYYY
 */
$app['defaults.date_format'] = 'ymd';

// Default first day of week. Options: 0 (Sunday), 1 (Monday)
$app['defaults.weekstart'] = 0;

// Logout redirection. Optional
$app['logout.redirection'] = '';

/**
 * End of default AgenDAV settings
 */

// Load configuration settings
require __DIR__ . '/settings.php';

// Configure logging (2/2). Needs log.path
$app['monolog.logfile'] = $app['log.path'] . '/' . date('Y-m-d') . '.log';

$app['locale'] = $app['defaults.language'];
