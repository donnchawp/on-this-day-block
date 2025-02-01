<?php
/**
 * Plugin Name: On This Day Block
 * Description: A Gutenberg block that displays posts published on this day in previous years.
 * Version: 1.0
 * Author: Donncha O Caoimh
 *
 * @package OnThisDay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OnThisDayBlock {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_shortcode( 'on-this-day', array( $this, 'handle_shortcode' ) );
	}

	/**
	 * Register the block assets for both frontend and backend.
	 */
	public function register_block() {
		// Register the block editor script.
		wp_register_script(
			'otd-block-script',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
		);

		// Register and enqueue frontend styles.
		wp_register_style(
			'otd-block-style',
			plugins_url( 'style.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
		);

		// Register and enqueue frontend script.
		wp_register_script(
			'otd-carousel-script',
			plugins_url( 'carousel.js', __FILE__ ),
			array( 'jquery' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'carousel.js' ),
			true
		);

		// Register the block type.
		register_block_type(
			'otd/on-this-day',
			array(
				'editor_script'   => 'otd-block-script',
				'style'          => 'otd-block-style',
				'script'         => 'otd-carousel-script',
				'render_callback' => array( $this, 'render_block' ),
				'supports'        => array(
					'align'           => true,
					'spacing'         => array(
						'margin'  => true,
						'padding' => true,
					),
					'customClassName' => true,
				),
			)
		);
	}

	/**
	 * Server-side render callback for the block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output for the block.
	 */
	public function render_block( $attributes ) {
		// Get alignment class if set
		$align_class = isset( $attributes['align'] ) ? 'align' . $attributes['align'] : '';
		
		// Start output buffering with alignment class
		$output = sprintf( '<div class="otd-block %s">', esc_attr( $align_class ) );

		// Get today's month, day, and year.
		$current_day   = date( 'j' );
		$current_month = date( 'n' );
		$current_year  = date( 'Y' );

		// Set up query arguments.
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'date_query'     => array(
				array(
					'month' => $current_month,
					'day'   => $current_day,
				),
			),
		);

		$query = new WP_Query( $args );

		// If no posts were found.
		if ( ! $query->have_posts() ) {
			$output .= '<p>No posts found for this day.</p>';
			$output .= '</div>';
			return $output;
		}

		$output .= '<div class="otd-carousel-container">';
		$output .= '<button class="otd-nav-button prev" aria-label="Previous posts">&larr;</button>';
		$output .= '<div class="otd-carousel">';
		$output .= '<ul class="otd-post-list">';

		// Loop through the posts.
		while ( $query->have_posts() ) {
			$query->the_post();

			$post_year = get_the_date( 'Y' );

			// Exclude posts from the current year.
			if ( $post_year == $current_year ) {
				continue;
			}

			$output .= '<li>';
			if ( has_post_thumbnail() ) {
				$output .= '<div class="otd-thumbnail">';
				$output .= '<a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( null, 'thumbnail' ) . '</a>';
				$output .= '</div>';
			} else {
				$post_content = get_the_content();
				preg_match( '/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $post_content, $matches );
				
				if ( ! empty( $matches[1] ) ) {
					$output .= '<div class="otd-thumbnail">';
					$output .= '<a href="' . esc_url( get_permalink() ) . '"><img src="' . esc_url( $matches[1] ) . '" alt="" class="wp-post-image" /></a>';
					$output .= '</div>';
				} else {
					$output .= '<div class="otd-thumbnail">';
					$output .= substr( strip_tags( get_the_content() ), 0, 50 );
					$output .= '</div>';
				}
			}
			$output .= '<div class="otd-post-content">';
			$output .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
			$output .= ' <span class="otd-post-year">(' . esc_html( $post_year ) . ')</span>';
			$output .= '</div>';
			$output .= '</li>';
		}
		wp_reset_postdata();

		$output .= '</ul>';
		$output .= '</div>';
		$output .= '<button class="otd-nav-button next" aria-label="Next posts">&rarr;</button>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Handle the [on-this-day] shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the shortcode.
	 */
	public function handle_shortcode( $atts ) {
		// Ensure scripts and styles are enqueued
		wp_enqueue_style( 'otd-block-style' );
		wp_enqueue_script( 'otd-carousel-script' );

		// Convert shortcode attributes to block attributes format
		$attributes = shortcode_atts(
			array(
				'align' => '',
			),
			$atts
		);

		return $this->render_block( $attributes );
	}

	/**
	 * Initialize the plugin
	 */
	public static function init() {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new self();
		}
		return $instance;
	}
}

// Initialize the plugin
OnThisDayBlock::init();
