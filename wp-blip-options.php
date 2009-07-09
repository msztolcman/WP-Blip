<?php
/*
 * Interface file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 * $Id$
 */

if (!defined ('WP_BLIP')) exit;

require_once 'wp-blip-common.php';

$wp_blip_options = wp_blip_get_options ();

?>
<style type="text/css">
div.wp_blip dt {
    font-style: italic;
}
div.wp_blip dd {
    padding-left: 1em;
}
</style>
<div class="wrap wp_blip">
    <h2>WP-Blip!</h2>
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options') ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="wp_blip_login">Użytkownik w serwisie <a href="http://blip.pl">Blip!</a>:</label></th>
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
					Przykład: &lt;ul class=&quot;blip_log&quot;&gt;
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
				<th scope="row">Sposób wyświetlania daty statusu:</th>
                <td>
                    <label for="wp_blip_datetype_relative"><input
                        type="radio" name="wp_blip_datetype" value="relative" id="wp_blip_datetype_relative"
                        <?php echo $wp_blip_options['datetype'] == 'relative' ? 'checked="checked"' : ''; ?>
                        /> relatywny</label>
                    <label for="wp_blip_datetype_relative_simple"><input
                        type="radio" name="wp_blip_datetype" value="relative_simple" id="wp_blip_datetype_relative_simple"
                        <?php echo $wp_blip_options['datetype'] == 'relative_simple' ? 'checked="checked"' : ''; ?>
                        /> relatywny (uproszczony)</label>
                    <label for="wp_blip_datetype_absolute"><input
                        type="radio" name="wp_blip_datetype" value="absolute" id="wp_blip_datetype_absolute"
                        <?php echo $wp_blip_options['datetype'] == 'absolute' ? 'checked="checked"' : ''; ?>
                        /> absolutny</label><br />

                    Przykłady:
                    <dl>
                        <dt>relatywny:</dt>
                        <dd>2 minuty</dd>
                        <dd>3 godziny, 2 minuty</dd>
                        <dd>4 dni, 7 godzin, 4 minuty</dd>
                        <dt>relatywny uproszczony:</dt>
                        <dd>2 minuty</dd>
                        <dd>3 godziny</dd>
                        <dd>4 dni</dd>
                        <dt>absolutny:</dt>
                        <dd>20.12.2009 20:01:37</dd>
                    </dl>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_dateformat">Szablon daty:</label></th>
                <td><input type="text" name="wp_blip_dateformat" id="wp_blip_dateformat"
                    value="<?php echo htmlentities2 ($wp_blip_options['dateformat']) ?>"
                    size="50" <?php $wp_blip_options['datetype'] == 'relative' ? 'disabled="disabled"' : ''; ?> /><br />
					Szczegóły: <a href="http://php.net/strftime">php.net/strftime</a>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Rozwiń linki <code>rdir.pl</code>:</th>
                <td>
                    <label for="wp_blip_expand_rdir_yes"><input
                        type="radio" name="wp_blip_expand_rdir" id="wp_blip_expand_rdir_yes" value="1"
                        <?php echo $wp_blip_options['expand_rdir'] ? 'checked="checked"' : ''; ?>
                        /> rozwiń</label>
                    <label for="wp_blip_expand_rdir_no"><input
                        type="radio" name="wp_blip_expand_rdir" id="wp_blip_expand_rdir_no" value="0"
                        <?php echo $wp_blip_options['expand_rdir'] ? '' : 'checked="checked"'; ?>
                        /> nie rozwijaj</label><br />
                    Rozwijanie linków nieco spowalnia pobieranie listy statusów.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Wczytaj linkowane statusy:</th>
                <td>
                    <label for="wp_blip_expand_linked_statuses_yes"><input
                        type="radio" name="wp_blip_expand_linked_statuses" id="wp_blip_expand_linked_statuses_yes" value="1"
                        <?php echo $wp_blip_options['expand_linked_statuses'] ? 'checked="checked"' : ''; ?>
                        /> wczytaj</label>
                    <label for="wp_blip_expand_linked_statuses_no"><input
                        type="radio" name="wp_blip_expand_linked_statuses" id="wp_blip_expand_linked_statuses_no" value="0"
                        <?php echo $wp_blip_options['expand_linked_statuses'] ? '' : 'checked="checked"'; ?>
                        /> nie wczytuj</label><br />
                    Wczytywanie linkowanych statusów nieco spowalnia pobieranie listy statusów.
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
		    <input type="hidden" name="page_options" value="wp_blip_login,wp_blip_quant,wp_blip_time,wp_blip_tpl,wp_blip_dateformat,wp_blip_tags,wp_blip_tpl_container_pre,wp_blip_tpl_container_post,wp_blip_expand_rdir,wp_blip_expand_linked_statuses,wp_blip_datetype" />
        </p>
	</form>
</div>

