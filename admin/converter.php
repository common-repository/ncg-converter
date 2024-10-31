<?php

namespace Almiro\Wordpress\Nextcellent\Converter\Admin;

use Almiro\Wordpress\Nextcellent\Converter\Attach_To_Post_Converter;

/**
 * Render the page.
 */
class Converter_Page {

	/**
	 * Handle any post / get parameters, and database code and such.
	 *
	 * @return array The data to pass to the HTML.
	 */
	public function request_handler() {

		if ( isset( $_POST['form'] ) && $_POST['form'] === "manual" ) {

			check_admin_referer( 'convert-pages' );

			$to_album = isset( $_POST['to-album'] );
			$prefetch = isset( $_POST['prefetch'] );

			if ( ! isset( $_POST['number'] ) || (int) $_POST['number'] === 0 ) {
				$args = [
					'albums'   => $to_album,
					'prefetch' => $prefetch
				];
			} else {
				$args = [
					'albums'   => $to_album,
					'number'   => (int) $_POST['number'],
					'prefetch' => $prefetch
				];
			}

			$converter = new Attach_To_Post_Converter( $args );
			$posts     = $converter->get_posts();
			$errors    = $converter->convert_all( $posts );

			if ( ! empty( $errors ) ) {
				return [
					'message' => 'Something went wrong with posts ' . implode( ', ', $errors )
				];
			}

			if ( count( $posts ) > 0 ) {
				$message = sprintf( 'Converted %s posts.', count( $posts ) );
			} else {
				$message = 'Nothing to convert.';
			}

			return [
				'message' => $message
			];
		}

		return [ ];
	}

	/**
	 * Render the HTML.
	 *
	 * @param array $data The data that should be passed to the html.
	 */
	public function render( $data ) {
		?>
		<div class="wrap">
			<h2>Convertor</h2>
			<?php if ( isset( $data['message'] ) ): ?>
				<div class="updated notice below-h2">
					<p><?php echo $data['message']; ?></p>
				</div>
			<?php endif; ?>
			<?php settings_errors(); ?>

			<h3>Convert tags</h3>

			<p>Press the 'Convert' button to convert some posts. If you have a lot of posts, you might need to do this
				multiple times.</p>

			<p>Use the add as filter option below to preview what the result would be: enable it and go to a post to see
				the result.</p>

			<form method="post">
				<?php wp_nonce_field( 'convert-pages' ); ?>
				<input type="hidden" name="form" value="manual">

				<table class="form-table">
					<tr>
						<th scope="row"><label for="number">Number of posts</label></th>
						<td>
							<input type="number" id="number" min="0" value="0" name="number" required>

							<p class="description">
								<strong>Please do not set this number too high.</strong> This <strong>WILL</strong>
								cause problems, as PHP will run into time-outs and memory problems.<br>
								This is due to the fact that for each post multiple database connections are
								necessary.<br>
								You should start at a decent amount, like 100 (the default), and try adding more a
								little at a time to see what works for you.<br>
								If you leave it on 0, the default will be used.
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Prefetch posts</th>
						<td>
							<input id="prefetch" name="prefetch" type="checkbox" value="1"> <label for="prefetch">Fetch
								all posts in one go.</label><br>

							<p class="description">
								This will get all hidden NextGEN posts in one go, as opposed to getting them one by one
								for every post to convert.<br>
								While this will be faster, it will consume more memory. How much more depends on how
								many post you have.<br>
								For example, if you have 1500 posts to convert, it will load all 1500 hidden NextGEN
								posts, only to use x of them (x the amount from above).<br>
								However, if you turn it of, a new database lookup must be done for each post, so 100
								database look ups.<br>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Convert to album</th>
						<td>
							<input id="to-album" name="to-album" type="checkbox" value="1"> <label for="to-album">Convert
								multiple galleries to albums.</label><br>

							<p class="description">
								NextGEN supports a gallery display type where you can select multiple galleries as a
								source.<br>
								NextCellent does <strong>NOT</strong> support that.<br>
								You have two choices:<br>
								<strong>- No conversion</strong> - This will convert it to multiple shortcodes. A
								gallery display with 3 galleries will be converted to 3 shortcodes.
								<br><strong>- Conversion</strong> - This will convert a gallery with multiple galleries
								to one album and display that. This will make a new album.
								<br>Note that this will be even slower.
							</p>
						</td>
					</tr>
				</table>

				<p>
					Note: depending on how many posts you selected and the database speed, this can take a very long
					time. Please be patient.<br>
					<span style="color: red">Please make a backup of your posts!</span>
				</p>

				<?php submit_button( 'Convert Pages' ) ?>
			</form>

			<hr>
			<form action="options.php" method="post">
				<?php settings_fields( 'ngg_converter_options' ); ?>
				<?php do_settings_sections( 'converter' ); ?>

				<?php submit_button() ?>
			</form>

			<p>Note: in the future, this page will move to under the NextCellent menu.</p>

		</div>

		<?php
	}
}