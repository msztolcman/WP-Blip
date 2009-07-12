<?php
/*
 * Plugin Name: WP-Blip!
 * Plugin URI: http://wp-blip.googlecode.com
 * Description: Wtyczka wyświetla ostatnie wpisy z <a href="http://blip.pl">blip.pl</a> (<a href="options-general.php?page=WP-Blip">skonfiguruj</a>).
 * Version: 0.5.1
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

if (class_exists ('WP_Widget')) {
    class WPBlip_Widget extends WP_Widget {
        function WPBlip_Widget() {
            $widget_opts    = array('classname' => 'wp_blip_widget', 'description' => 'Wyświetl statusy z Blip!a' );
            parent::WP_Widget (false, $name = 'WP-Blip!', $widget_opts);
        }

        function widget($args, $instance) {
            echo $args['before_widget'];
            echo $args['before_title'].
                $instance['title'].
                $args['after_title'];

            wp_blip ("\n", true);

            echo $args['after_widget'];
        }

        function update ($new_instance, $old_instance) {
            return $new_instance;
        }

        function form ($instance) {
            $title = esc_attr ($instance['title']);

            printf ('<p><label for="%s">%s<input class="widefat" id="%s" name="%s" type="text" value="%s" /></label></p>',
                $this->get_field_id ('title'),
                _e ('Title:'),
                $this->get_field_id ('title'),
                $this->get_field_name ('title'),
                $title
            );

            printf ('<p><a href="%s/wp-admin/options-general.php?page=WP-Blip">Więcej ustawień</a></p>',
                get_bloginfo ('wpurl')
            );
        }
    }

  