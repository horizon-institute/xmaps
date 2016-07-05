<?php
/**
 * Display fragment for Map Object post edit screen when the
 * Google Maps API key is not configured.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

?>
<ul>
<?php foreach ( $collections as $collection ) { ?>
	<li>
		<label class="selectit">
			<input value="<?php echo esc_attr( $collection[0]->ID ); ?>" 
					type="checkbox"
				<?php if ( true === $collection[1] ) { ?>
					checked="checked"
				<?php } ?>
					name="xmaps-map-collection-entry[]" />
			<?php echo esc_html( $collection[0]->post_title ); ?>
		</label>
	</li>
<?php } ?>
</ul>
