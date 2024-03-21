<?php
$GLOBALS['RAS']['PATH'] = plugin_dir_path(__FILE__);
$GLOBALS['RAS']['PLUGIN_FILENAME'] = basename('ras.php');

require_once $GLOBALS['RAS']['PATH'] . 'src/Utilities/RasSettings.php';
require_once $GLOBALS['RAS']['PATH'] . 'src/Hooks/CheckVersionHook.php';
require_once $GLOBALS['RAS']['PATH'] . 'src/Hooks/ProductHook.php';
require_once $GLOBALS['RAS']['PATH'] . 'src/Utilities/ProductUtil.php';
require_once $GLOBALS['RAS']['PATH'] . 'src/Exceptions/RasBlockNotFoundException.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';