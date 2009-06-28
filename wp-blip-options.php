<?php
/*
 * Interface file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

require_once 'wp-blip-common.php';

$wp_blip_options = wp_blip_get_options ();

?>
<div class="wrap">
    <h2>WP-Blip!</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options') ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="wp_blip_login">Użytkownik w seriwsie <a href="http://blip.pl">Blip!</a>:</label></th>
				<td><input type="text" name="wp_blip_login" id="wp_blip_login" value="<?php echo htmlentities2 (get_option ('wp_blip_login')) ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_quant">Ilość statusów do pobrania:</label></th>
				<td><input type="text" name="wp_blip_quant" id="wp_blip_quant" value="<?php echo htmlentities2 ($wp_blip_options['quant']) ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_time">Okres trwałości pamięci podręcznej:</label></th>
				<td><input type="text" name="wp_blip_time" id="wp_blip_time" value="<?php echo htmlentities2 ($wp_blip_options['time']) ?>" /><br />
					W sekundach</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_tags">Śledź tylko wymienione tagi:</label></th>
				<td><input type="text" name="wp_blip_tags" id="wp_blip_tags" value="<?php echo htmlentities2 ($wp_blip_options['tags']) ?>" /><br />
					rozdzielaj poszczególne tagi znakiem spacji lub zostaw puste jeśli nie chcesz filtrować statusów</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_tpl_container_pre">Przed listą statusów wstaw:</label></th>
				<td><input type="text" name="wp_blip_tpl_container_pre" id="wp_blip_tpl_container_pre" value="<?php echo htmlentities2 ($wp_blip_options['tpl_container_pre']) ?>" size="50"/><br />
					Przykład: &lt;ul class="blip_log"&gt;
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_tpl_container_post">Po liście statusów wstaw:</label></th>
				<td><input type="text" name="wp_blip_tpl_container_post" id="wp_blip_tpl_container_post" value="<?php echo htmlentities2 ($wp_blip_options['tpl_container_post']) ?>" size="50"/><br />
					Przykład: &lt;/ul&gt;
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_tpl">Szablon wiadomości:</label></th>
				<td><input type="text" name="wp_blip_tpl" id="wp_blip_tpl" value="<?php echo htmlentities2 ($wp_blip_options['tpl']) ?>" size="50"/><br />
					%url - zostanie zastąpione permalinkiem do statusu<br />
					%body - treść statusu<br />
					%date - data ustawienia statusu<br />
					Przykład: &lt;li&gt;&lt;h4&gt;&lt;a href=&quot;%url&quot;&gt;%date&lt;/a&gt;&lt;/h4&gt;&lt;br /&gt;%body&lt;/li&gt;
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_dateformat">Szablon daty:</label></th>
				<td><input type="text" name="wp_blip_dateformat" id="wp_blip_dateformat" value="<?php echo htmlentities2 ($wp_blip_options['dateformat']) ?>" size="50"/><br />
					Szczegóły: <a href="http://php.net/strftime">php.net/strftime</a>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Wyczyść cache:</th>
                <td><a href="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-blip/wp-blip-ajax.php?ajax=1&amp;action=cache_invalidate"
                    onclick="jQuery.get (this.href, {}, function (d, s) {alert (d);}); return false">wyczyść</a></td>
			</tr>
		</table>
		<p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		    <input type="hidden" name="action" value="update" />
		    <input type="hidden" name="page_options" value="wp_blip_login,wp_blip_quant,wp_blip_time,wp_blip_tpl,wp_blip_dateformat,wp_blip_tags,wp_blip_tpl_container_pre,wp_blip_tpl_container_post" />
        </p>
	</form>
</div>

