<?php
/**
 * Define callback functions for templater
 *
 * @package   Cherry_Team_Members
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2015 Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Callbcks for team shortcode templater
 *
 * @since  1.0.0
 */
class Cherry_Team_Members_Template_Callbacks {

	/**
	 * Shortcode attributes array
	 * @var array
	 */
	public $atts = array();

	/**
	 * Specific post data
	 * @var array
	 */
	public $post_data = array();

	/**
	 * Constructor for the class
	 *
	 * @since 1.0.0
	 * @param array $atts input attributes array.
	 */
	function __construct( $atts ) {
		$this->atts = $atts;
	}

	/**
	 * Clear post data after loop iteration
	 *
	 * @since  1.0.3
	 * @return void
	 */
	public function clear_data() {
		$this->post_data = array();
	}

	/**
	 * Get post title
	 *
	 * @since  1.0.3
	 * @return string
	 */
	public function post_title() {
		if ( ! isset( $this->post_data['title'] ) ) {
			$this->post_data['title'] = get_the_title();
		}
		return $this->post_data['title'];
	}

	/**
	 * Get post permalink
	 *
	 * @since  1.0.3
	 * @return string
	 */
	public function post_permalink() {
		if ( ! isset( $this->post_data['permalink'] ) ) {
			$this->post_data['permalink'] = get_permalink();
		}
		return $this->post_data['permalink'];
	}

	/**
	 * Get the image for the given ID. If no featured image, check for Gravatar e-mail.
	 *
	 * @since  1.0.0
	 * @param  string $size Image size.
	 * @return string
	 */
	public function post_image( $size = null ) {

		global $post;

		if ( ! isset( $this->post_data['image'] ) ) {

			if ( ! has_post_thumbnail( $post->ID ) ) {
				return false;
			}

			$this->post_data['image'] = '';

			if ( ! $size ) {
				// If not a string or an array, and not an integer, default to 150x9999.
				$size = isset( $this->atts['size'] ) ? $this->atts['size'] : 150;
			}

			if ( is_integer( $size ) ) {
				$size = array( $size, $size );
			} elseif ( ! is_string( $size ) ) {
				$size = 'thumbnail';
			}

			$this->post_data['image'] = get_the_post_thumbnail(
				intval( $post->ID ),
				$size,
				array( 'class' => 'avatar', 'alt' => $this->post_title() )
			);
		}

		return $this->post_data['image'];
	}

	/**
	 * Get post thumbnail
	 *
	 * @since  1.0.0
	 * @param  string $atts is linked image or not.
	 * @return string
	 */
	public function get_photo( $args = array() ) {

		global $post;

		$args = wp_parse_args( $args, array(
			'size' => 'thumbnail',
			'link' => true
		) );

		$photo = $this->post_image();

		if ( ! $photo ) {
			return;
		}

		$args['link'] = filter_var( $args['link'], FILTER_VALIDATE_BOOLEAN );

		if ( true === $args['link'] ) {
			$format = '<a href="%2$s">%1$s</a>';
			$link   = $this->post_permalink();
		} else {
			$format = '%1$s';
			$link   = false;
		}

		if ( true === $this->atts['show_photo'] || 'yes' === $this->atts['show_photo'] ) {
			return sprintf( $format, $photo, $link );
		}

	}

	/**
	 * Get team memeber name (post title)
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_name() {
		global $post;
		if ( true === $this->atts['show_name'] ) {
			return $this->post_title();
		}
	}

	/**
	 * Get team member position
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_position() {
		return $this->get_meta_html( 'position' );
	}

	/**
	 * Get team member location
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_location() {
		return $this->get_meta_html( 'location' );
	}

	/**
	 * Get team member phone number
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_phone() {
		return $this->get_meta_html( 'telephone' );
	}

	public function get_meta_html( $meta ) {
		global $post;
		$value = get_post_meta( $post->ID, 'cherry-team-' . $meta, true );
		return ( ! empty( $value ) ) ? $this->meta_wrap( $value, $meta ) : '';
	}

	/**
	 * Get post exerpt
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_excerpt() {

		global $post;

		$excerpt = has_excerpt( $post->ID ) ? apply_filters( 'the_excerpt', get_the_excerpt() ) : '';

		if ( ! $excerpt ) {

			$excerpt_length = ( ! empty( $this->atts['excerpt_length'] ) )
								? $this->atts['excerpt_length']
								: 20;

			$content = get_the_content();
			$excerpt = strip_shortcodes( $content );
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
			$excerpt = wp_trim_words( $excerpt, $excerpt_length, '' );

		}

		$format = '<div class="cherry-team_excerpt">%s</div>';

		return sprintf( $format, $excerpt );

	}

	/**
	 * Get post content
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_content() {

		$content = apply_filters( 'the_content', get_the_content() );

		if ( ! $content ) {
			return;
		}

		$format = '<div class="post-content">%s</div>';

		return sprintf( $format, $content );
	}

	/**
	 * Get team memeber socials list
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_socials() {

		global $post;
		$socials = get_post_meta( $post->ID, 'cherry-team-social', true );

		$defaults = array(
			'icon'  => '',
			'label' => '',
			'url'   => '',
		);

		// Global item format
		$format = apply_filters(
			'cherry_team_socials_item_format',
			'<div class="team-socials_item"><a href="%3$s" class="team-socials_link" rel="nofollow">%1$s<span class="team-socials_label">%2$s</span></a></div>'
		);

		// Icon format
		$icon_format = apply_filters(
			'cherry_team_social_icon_format',
			'<i class="team-socials_icon fa %s"></i>'
		);

		$result = '';

		foreach ( $socials as $data ) {

			$data  = wp_parse_args( $data, $defaults );
			$url   = esc_url( $data['url'] );
			$icon  = sprintf( $icon_format, esc_attr( $data['icon'] ) );
			$label = esc_attr( $data['label'] );

			$label = sprintf( $label, $this->post_title() );

			$result .= sprintf( $format, $icon, $label, $url );

		}

		return '<div class="team-socials">' . $result . '</div>';

	}

	/**
	 * Get link URL to team member page
	 */
	public function get_link() {
		global $post;
		return $this->post_permalink();
	}

	/**
	 * Wrap single team item into HTML wrapper with custom class
	 *
	 * @since  1.0.0
	 * @param  string $value meta value.
	 * @param  string $class custom CSS class.
	 * @return string
	 */
	public function meta_wrap( $value = null, $class = null ) {

		if ( ! $value ) {
			return;
		}

		$css_class = 'team-meta_item';

		if ( $class ) {
			$css_class .= ' ' . sanitize_html_class( $class );
		}

		return sprintf( '<span class="%s">%s</span>', $css_class, $value );

	}

}
