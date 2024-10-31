<?php

namespace Almiro\Wordpress\Nextcellent\Converter;

use nggdb;

/**
 * Convert attach to post NextGEN stuff.
 */
class Attach_To_Post_Converter {

	/**
	 * The attach to post slug used by NextGen.
	 * This is defined in the module.attach_to_post.php file (photocrati-attach_to_post module).
	 */
	const NGG_ATTACH_TO_POST_SLUG = 'nextgen-attach_to_post';

	private $args;
	private $ngg_options;
	private $posts_cache;

	public function __construct($args = []) {

		$defaults = [
			'albums'    => false,
			'number'    => 100,
			'prefetch'  => false
		];

		$this->args = wp_parse_args($args, $defaults);
		$this->ngg_options = get_option('ngg_options');
		$this->posts_cache = [];

		if($this->args['albums']) {
			require_once(NGGALLERY_ABSPATH . '/lib/ngg-db.php');
		}
	}

	/**
	 * Get the posts that contain the slug.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_posts() {
		$finder = new Post_Finder();
		$args   = [
			'posts_per_page' => $this->args['number']
		];
		$posts  = $finder->get( '%' . self::NGG_ATTACH_TO_POST_SLUG . '%', $args );

		return $posts;


	}

	/**
	 * Get all custom hidden NGG posts
	 *
	 * @return array The posts.
	 */
	private function get_ngg_posts() {

		$temp_array = get_posts([
			'posts_per_page' => -1,
			'post_type' => 'displayed_gallery',
			'post_status' => 'draft',
			'suppress_filters' => true
		]);

		$ngg_posts = [];

		foreach($temp_array as $temp_post) {
			$ngg_posts[$temp_post->ID] = $temp_post;
		}

		return $ngg_posts;
	}

	/**
	 * Convert the posts to NextCellent shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $posts The posts to use.
	 *
	 * @return array An array of ID's for which an error occurred.
	 */
	public function convert_all($posts = null) {

		if($this->args['prefetch']) {
			$this->posts_cache = $this->get_ngg_posts();
		}

		if($posts === null) {
			$posts = self::get_posts();
		}

		$errors = [];

		foreach ( $posts as $post ) {

			$update = [
				'ID'    => $post->ID,
				'post_content' => $this->convert_one($post->post_content)
			];

			if(wp_update_post($update) === 0) {
				array_push($errors, $post->ID);
			}
		}

		return $errors;
	}

	/**
	 * Converts the NextGEN stuff in the given content to NextCellent stuff. This function is usable with
	 * the 'the_content' filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_content The content of the post.
	 *
	 * @return string The new content.
	 */
	public function convert_one($post_content) {

		if ( preg_match_all( "#<img.*http(s)?://(.*)?" . self::NGG_ATTACH_TO_POST_SLUG . "(=|/)preview(/|&|&amp;)id(=|--)(\\d+).*?>#mi",
			$post_content, $matches, PREG_SET_ORDER ) ) {

			$replacements = [];

			//For every match in the post.
			foreach ( $matches as $match ) {

				//Get the hidden post with the content.
				$ngg_post = $this->get_post( $match[6] );

				//Get the data.
				$data = Helpers::unserialize( $ngg_post->post_content );

				//The output
				array_push($replacements, $this->converter($data['container_ids'], $data['display_type'], $data['display_settings'], $data['source']));
			}

			//We make an array containing the pattern as many times as there are replacements.
			$patterns = $this->make_pattern_array(count($replacements));

			//Return the adjusted content
			return preg_replace($patterns, $replacements, $post_content, 1);
		} else {
			return $post_content;
		}
	}

	/**
	 * Make an array containing the pattern a $number of times.
	 *
	 * @since 1.0.0
	 *
	 * @param int $number The number of times the pattern should be in it.
	 *
	 * @return array Contains the $number patterns.
	 */
	private function make_pattern_array($number) {
		return array_fill(0, $number, "#<img.*http(s)?://(.*)?" . self::NGG_ATTACH_TO_POST_SLUG . "(=|/)preview(/|&|&amp;)id(=|--)(\\d+).*?>#mi");
	}

	/**
	 * Convert galleries.
	 *
	 * @since 1.0.0
	 *
	 * @param array $ids             The gallery ID's.
	 * @param string $type           The type.
	 * @param array $display_options The NextGEN display options.
	 * @param string $source The source of the data.
	 *
	 * @return string
	 */
	private function converter($ids, $type, $display_options, $source) {

		//The name of the shortcode.
		$name = '';
		//The options of the shortcode.
		$options = '';
		//The ID container
		$id_text = 'id';

		switch($type) {
			//The slideshow
			case 'photocrati-nextgen_basic_slideshow':
				$name = 'slideshow';
				if($display_options['gallery_width'] != $this->ngg_options['irWidth']) {
					$options = ' w=' . $display_options['gallery_width'];
				}
				if($display_options['gallery_height'] != $this->ngg_options['irHeight']) {
					$options .= ' h=' . $display_options['gallery_width'];
				}
				break;
			//The image browser
			case 'photocrati-nextgen_basic_imagebrowser':
				$name = 'imagebrowser';
				break;
			//Tag clouds
			case 'photocrati-nextgen_basic_tagcloud':
				if($source !== 'galleries') {
					return '';
				}

				$name = 'nggtags';
				$id_text = 'gallery';
				break;
			//Compact albums
			case 'photocrati-nextgen_basic_compact_album':

				$name = 'nggalbum';
				$options = ' template=compact';

				break;
			//Extended albums
			case 'photocrati-nextgen_basic_extended_album':
				$name = 'nggalbum';
				$options = ' template=extend';
				break;
			//Basic galleries
			case 'photocrati-nextgen_basic_thumbnails':
				if($this->args['albums'] && count($ids) > 1) {
					return $this->convert_and_save_to_album($ids);
				} else {
					$name = 'nggallery';
					if($display_options['images_per_page'] != $this->ngg_options['galImages']) {
						$options = ' images=' . $display_options['images_per_page'];
					}
				}
				break;
			//Something else
			default:
				return '';
		}

		//This is for shortcodes that use an ID (or are not returned above).
		$output = '';

		foreach($ids as $id) {
			$output .= "[$name $id_text=$id$options]\n";
		}

		return $output;
	}

	/**
	 * Convert multiple galleries to an album and display that.
	 *
	 * @since 1.0.0
	 *
	 * @param array $ids The gallery ID's.
	 *
	 * @return string
	 */
	private function convert_and_save_to_album($ids) {

		$id = nggdb::add_album(false, 0, "", serialize($ids));

		return "[nggalbum id=$id template=compact]";
	}

	/**
	 * Get a post. If it is available in the cache, take that. Otherwise look it up (and add it to the cache.
	 *
	 * @param int $id
	 *
	 * @return \WP_Post The post.
	 */
	private function get_post($id) {

		if(!array_key_exists($id, $this->posts_cache)) {
			$this->posts_cache[$id] = get_post($id);
		}

		return $this->posts_cache[$id];

	}
}