<?php

namespace Almiro\Wordpress\Nextcellent\Converter;

/**
 * Find a post.
 */
class Post_Finder {

	private $content = '';

	private $like = true;

	/**
	 * Mapper for get_posts() with extra arguments 'content' and 'like'
	 *
	 * 'content' must be a string with optional '%' for free values.
	 *
	 * @param $content
	 *
	 * @return array
	 */
	public function get( $content, $args ) {

		$this->content = $content;

		add_filter( 'posts_where', [ $this, 'where_filter' ] );

		$args['content']          = $this->content;
		$args['suppress_filters'] = false;

		return get_posts( $args );
	}

	/**
	 * Changes the WHERE clause.
	 *
	 * @param string $where
	 *
	 * @return string
	 */
	public function where_filter( $where ) {
		// Make sure we run this just once.
		remove_filter( 'posts_where', [ $this, 'where_filter' ] );

		global $wpdb;
		$like = $this->like ? 'LIKE' : 'NOT LIKE';
		// Escape the searched text.
		$extra = $wpdb->prepare( '%s', $this->content );

		return "$where AND post_content $like $extra";
	}
}