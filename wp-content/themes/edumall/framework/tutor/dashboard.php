<?php
defined( 'ABSPATH' ) || exit;

/**
 * Templates & hooks for Dashboard page.
 */
if ( ! class_exists( 'Edumall_Tutor_Dashboard' ) ) {
	class Edumall_Tutor_Dashboard {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_filter( 'body_class', [ $this, 'body_class' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
			add_filter( 'edumall_top_bar_type', [ $this, 'hide_top_bar' ] );
			add_filter( 'edumall_header_type', [ $this, 'hide_header' ] );
			add_filter( 'edumall_title_bar_type', [ $this, 'hide_title_bar' ] );
			add_filter( 'edumall_footer', [ $this, 'hide_footer' ] );
			add_filter( 'edumall_sidebar_1', [ $this, 'hide_sidebar' ] );

			add_action( 'wp_footer', [ $this, 'add_dashboard_nav_template' ] );
			add_action( 'wp_footer', [ $this, 'add_public_profile_nav_template' ] );

			/**
			 * Add custom link to Dashboard menu
			 *
			 * @since 2.7.4
			 */
			add_filter( 'tutor_dashboard/instructor_nav_items', [ $this, 'add_my_students_nav_item' ] );
		}

		public function add_my_students_nav_item( $nav_items ) {
			$nav_items['my-students'] = array(
				'title'    => __( 'My Students', 'edumall' ),
				'auth_cap' => tutor()->instructor_role,
			);

			return $nav_items;
		}

		public function body_class( $classes ) {
			global $wp_query;

			$dashboard_page_slug = '';
			$dashboard_page_name = '';
			if ( isset( $wp_query->query_vars['tutor_dashboard_page'] ) && $wp_query->query_vars['tutor_dashboard_page'] ) {
				$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
				$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
			}

			/**
			 * Getting dashboard sub pages
			 */
			if ( isset( $wp_query->query_vars['tutor_dashboard_sub_page'] ) && $wp_query->query_vars['tutor_dashboard_sub_page'] ) {
				if ( ! empty( $dashboard_page_slug ) ) {
					$dashboard_page_slug = $dashboard_page_slug . '-' . $wp_query->query_vars['tutor_dashboard_sub_page'];
				}
			}

			if ( Edumall_Tutor::instance()->is_dashboard() ) {
				$classes[] = "dashboard-{$dashboard_page_slug}-page";

				if ( is_user_logged_in() && 'create-course' !== $dashboard_page_name ) {
					$classes[] = 'dashboard-page';

					if ( ! Edumall::is_handheld() ) {
						$classes[] = 'dashboard-nav-fixed';
					}
				} else {
					$classes[] = 'required-login';
				}
			}

			return $classes;
		}

		public function frontend_scripts() {
			if ( Edumall_Tutor::instance()->is_dashboard() ) {
				wp_enqueue_script( 'edumall-grid-layout' );
			} else {
				/**
				 * These scripts enqueue on all pages.
				 * This hurt site performance.
				 * Should enqueue on Dashboard => Settings
				 *
				 * @since 2.8.1
				 */
				wp_dequeue_style( 'tutor-instructor-signature-css' );
				wp_dequeue_script( 'tutor-instructor-signature-js' );
			}
		}

		public function add_dashboard_nav_template() {
			global $wp_query;

			if ( $wp_query->is_page ) {
				if ( is_user_logged_in() && Edumall_Tutor::instance()->is_dashboard() && ! Edumall_Tutor::instance()->is_create_course() ) {
					tutor_load_template( 'dashboard.navs' );
					edumall_load_template( 'off-canvas' );
				}
			}
		}

		public function add_public_profile_nav_template() {
			global $wp_query;

			if ( ! empty( $wp_query->query['tutor_student_username'] ) ) {
				tutor_load_template( 'profile.navs' );
				edumall_load_template( 'off-canvas' );
			}
		}

		public function hide_top_bar( $type ) {
			if ( Edumall_Tutor::instance()->is_dashboard() && is_user_logged_in() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_header( $type ) {
			if ( Edumall_Tutor::instance()->is_dashboard() && is_user_logged_in() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_title_bar( $type ) {
			if ( Edumall_Tutor::instance()->is_dashboard() ) {
				if ( is_user_logged_in() ) {
					return 'none';
				} else {
					return '05';
				}
			}

			return $type;
		}

		public function hide_footer( $type ) {
			if ( Edumall_Tutor::instance()->is_dashboard() && is_user_logged_in() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_sidebar( $type ) {
			if ( Edumall_Tutor::instance()->is_dashboard() && is_user_logged_in() ) {
				return 'none';
			}

			return $type;
		}
	}
}
