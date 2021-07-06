<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Kungfu_Framework_Project' ) ) {
	class Kungfu_Framework_Project {
		function __construct() {
			add_action( 'init', array( $this, 'register_post_types' ), 1 );
		}

		function register_post_types() {

			$slug = apply_filters( 'insight_core_project_slug', 'project' );

			$labels = array(
				'name'               => _x( 'Projects', 'post type general name', 'insight-core' ),
				'singular_name'      => __( 'Project Item', 'insight-core' ),
				'view_item'          => __( 'View Projects', 'insight-core' ),
				'add_new_item'       => __( 'Add New Project', 'insight-core' ),
				'add_new'            => _x( 'Add New', 'project', 'insight-core' ),
				'new_item'           => __( 'Add New Project Item', 'insight-core' ),
				'edit_item'          => __( 'Edit Project Item', 'insight-core' ),
				'update_item'        => __( 'Update Project', 'insight-core' ),
				'all_items'          => __( 'All Projects', 'insight-core' ),
				'parent_item_colon'  => __( 'Parent Project Item:', 'insight-core' ),
				'search_items'       => __( 'Search Project', 'insight-core' ),
				'not_found'          => __( 'No project items found', 'insight-core' ),
				'not_found_in_trash' => __( 'No project items found in trash', 'insight-core' ),
			);

			$supports = array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'comments',
				'author',
				'revisions',
				'custom-fields',
			);

			register_post_type(
				'project',
				array(
					'labels'      => $labels,
					'supports'    => $supports,
					'public'      => true,
					'has_archive' => true,
					'rewrite'     => array(
						'slug' => $slug,
					),
					'can_export'  => true,
					'menu_icon'   => ( version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) ) ? 'dashicons-portfolio' : false,
				)
			);

			register_taxonomy(
				'project_category',
				'project',
				array(
					'hierarchical'      => true,
					'label'             => __( 'Categories', 'insight-core' ),
					'query_var'         => true,
					'rewrite'           => true,
					'show_admin_column' => true,
				)
			);
		}
	}

	new Kungfu_Framework_Project;
}
