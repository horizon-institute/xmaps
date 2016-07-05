<?php
/**
 * User functions.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

use RobotSnowfall\UUID;

/**
 * User functions.
 */
class XMapsUser {

	/**
	 * Gets a user's API key, generating one if it does not exist.
	 *
	 * @param WP_User $user Wordpress user object.
	 * @return string User API key.
	 */
	public static function get_api_key_by_user( $user ) {
		$key = get_user_meta( $user->ID, 'xmaps-api-key', true );
		if ( ! empty( $key ) ) {
			return $key;
		}
		$key = base64_encode( UUID::v4() );
		add_user_meta( $user->ID, 'xmaps-api-key', $key, true );
		return $key;
	}

	/**
	 * Gets a user from their API key.
	 *
	 * @param string $key API key.
	 * @return boolean|WP_User False if user does not exist, otherwise user object.
	 */
	public static function get_user_by_api_key( $key ) {
		$users = get_users( array(
			'meta_key' => 'xmaps-api-key',
			'meta_value' => $key,
		) );
		if ( ! $users || ! is_array( $users ) ) {
			return false;
		}
		return $users[0];
	}

	/**
	 * Show the API key in the user's profile.
	 *
	 * @param WP_User $user User object.
	 */
	public static function show_profile_fields( $user ) {
		?>
		<h3>App development</h3>
		<p>
			To track who is creating apps that use Wander Anywhere and to spot
			any free-riders, any calls to the Wander Anywhere API must include a
			valid Author's app key. Your app key is in the box below. Please do
			not share this key, as you are responsible for any use of the API
			tagged with this key.
		</p>
		<table class="form-table">
			<tr>
				<th><label>My app key</label></th>
				<td>
					<input type="text" readonly="readonly" 
						value="<?php echo esc_attr( self::get_api_key_by_user( $user ) ); ?>"
						class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}
}
?>
