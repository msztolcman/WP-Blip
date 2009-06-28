<?php
/*
 * Plugin Name: WP-Blip!
 * Plugin URI: http://wp-blip.googlecode.com
 * Description: Wtyczka wyświetla ostatnie wpisy z <a href="http://blip.pl">blip.pl</a> (<a href="options-general.php?page=WP-Blip">skonfiguruj</a>).
 * Version: 0.4.5
 * Author: Marcin 'MySZ' Sztolcman
 * Author URI: http://urzenia.net/
 * SVNVersion: $Id$
 */

/*  Copyright 2008-2009  Marcin Sztolcman  (email : marcin@urzenia.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define ('WP_BLIP_DEBUG', false);

require_once 'wp-blip-common.php';

function wp_blip_admin_actions () {
    add_options_page('WP Blip!', 'WP Blip!', 7, 'WP-Blip', 'wp_blip_option_page');
}

function wp_blip_option_page () {
	include_once 'wp-blip-options.php';
}

add_action('admin_menu', 'wp_blip_admin_actions');

