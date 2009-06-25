<?php
/*
 * Ajax response file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

require_once(dirname(__FILE__).'/../../../wp-config.php');
require_once 'wp-blip-common.php';

if (isset ($_GET['ajax']) && isset ($_GET['action'])) {
    switch ($_GET['action']) {
        case 'cache_invalidate':
            $success    = 'Cache został wyczyszczony.';
            $fail       = 'Nie powiodło się czyszczenie cache.';
            print wp_blip_cache_invalidate () ? $success : $fail;
        break;
    }
}

