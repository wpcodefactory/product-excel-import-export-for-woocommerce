<?php
/*
 * Plugin Name: Product Excel Import & Export for WooCommerce
 * Plugin URI: https://extend-wp.com/product-import-export-for-woocommerce-with-excel/
 * Description: WordPress Plugin to Import/Update/Export Simple products for WooCommerce in Bulk with Excel.
 * Version: 7.0.4
 * Author: WPFactory
 * Author URI: https://wpfactory.com
 * Text Domain: woo-product-excel-importer
 * Domain Path: /langs
 * WC requires at least: 2.2
 * WC tested up to: 10.4
 * Requires Plugins: woocommerce
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Created On: 10-05-2016
 * Updated On: 21-01-2026
 */

defined( 'ABSPATH' ) || exit;

defined( 'WPFACTORY_WC_PEIE_VERSION' ) || define( 'WPFACTORY_WC_PEIE_VERSION', '7.0.4' );

defined( 'WPFACTORY_WC_PEIE_FILE' ) || define( 'WPFACTORY_WC_PEIE_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpfactory-wc-peie.php';

if ( ! function_exists( 'wpfactory_wc_peie' ) ) {
	/**
	 * Returns the main instance of WPFactory_WC_PEIE to prevent the need to use globals.
	 *
	 * @version 7.0.0
	 * @since   7.0.0
	 */
	function wpfactory_wc_peie() {
		return WPFactory_WC_PEIE::instance();
	}
}

add_action( 'plugins_loaded', 'wpfactory_wc_peie' );

/**
 * role.
 */
$role = get_role( 'administrator' );
$role->add_cap( 'wpeieWoo' );

function woopei_js() {
	/** this function css and js files */

	wp_enqueue_style( 'woo-importer_css', plugins_url( '/css/woo-importer.css?v=34', __FILE__ ) );
	wp_enqueue_style( 'woo-importer_css' );

	wp_enqueue_script( 'woo-importer-xlsx', plugins_url( '/js/xlsx.js', __FILE__ ), array( 'jquery' ), null, true );
	wp_enqueue_script( 'woo-importer-xlsx' );
	wp_enqueue_script( 'woo-importer-filesaver', plugins_url( '/js/filesaver.js', __FILE__ ), array( 'jquery' ), null, true );
	wp_enqueue_script( 'woo-importer-filesaver' );

	wp_enqueue_script( 'woopei_js', plugins_url( '/js/woo-importer.js?v=34', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable' ), null, true );
	wp_enqueue_script( 'woopei_js' );

	$hide_rating = esc_js( get_option( 'woopei_hide_rating' ) );
	$woopei      = array(
		'RestRoot'    => esc_url_raw( rest_url() ),
		'hide_rating' => $hide_rating,
		'plugin_url'  => plugins_url( '', __FILE__ ),
		'siteUrl'     => site_url(),
		'ajax_url'    => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'wp_rest' ),
		'exportfile'  => plugins_url( '/js/tableexport.js', __FILE__ ),
	);
	wp_localize_script( 'woopei_js', 'woopei', $woopei );
}
add_action( 'admin_enqueue_scripts', 'woopei_js' );

require plugin_dir_path( __FILE__ ) . '/import.php';
require plugin_dir_path( __FILE__ ) . '/import_categories.php';
require plugin_dir_path( __FILE__ ) . '/export.php';

add_action( 'wp_ajax_woopei_process', 'woopei_process' );
add_action( 'wp_ajax_nopriv_woopei_process', 'woopei_process' );
add_action( 'wp_ajax_woopei_exportProducts', 'woopei_exportProducts' );
add_action( 'wp_ajax_nopriv_woopei_exportProducts', 'woopei_exportProducts' );

add_action( 'admin_footer', 'woopeiPopup' );

function woopei_header() {
	/** this function is main plugin header */

	?>
		<img src='<?php echo esc_url( plugins_url( 'images/woo_product_importer_banner.jpg', __FILE__ ) ); ?>'style='width:100%;'  />

	<?php
}

function woopei_footer() {
	/** this function is main plugin footer */
	?>
	<hr>

		<a target='_blank' class='web_logo' href='https://extend-wp.com/'>
			<img  src='<?php echo esc_url( plugins_url( 'images/extendwp.png', __FILE__ ) ); ?>' alt='Get more plugins by extendWP' title='Get more plugins by extendWP' />
		</a>
	<?php
}

function woopei_form() {
	/** this function main import form */
	?>
			<form method="post" id='product_import' enctype="multipart/form-data" action= "<?php echo esc_url( admin_url( 'admin.php?page=woo-product-importer' ) ); ?>">

				<table class="form-table">
					<tr valign="top">
					<th scope="row" style='width:100%;background:transparent'>
						<div class="uploader" style="background:url(<?php print esc_url( plugins_url( 'images/default.png', __FILE__ ) ); ?> ) no-repeat left center;" >
							<img src="" class='userSelected'/>
							<input type="file"  required name="file" class='woopeiFile' accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
						</div>
					</th>
					<input type="hidden"  name="importProducts" value="1" />
					<td><?php wp_nonce_field( 'excel_upload' ); ?></td>
					</tr>
				</table>

				<?php submit_button( 'Upload', 'primary', 'upload' ); ?>

			</form>
			<div class='result'>
				<?php woopei_importProducts(); ?>
			</div>
	<?php
}

function woopei_main() {
	/** this function provides the html for the main interface */
	?>

		<p>
			<strong><?php esc_html_e( 'Import / Update simple WooCommerce products.', 'woo-product-excel-importer' ); ?>
			<a href='<?php echo esc_url( plugins_url( '/sample_excel/import_products.xlsx', __FILE__ ) ); ?>'><?php esc_html_e( 'sample xlsx', 'woo-product-excel-importer' ); ?></a>
			</strong>
		</p>
		<?php

		woopei_Rating();

		woopei_form();
}

// MAIN FORM FOR EXCEL UPLOAD
function woopei_init() {
	/** this function initializes the main interface */
	?>
	<div class="importer-wrap">

	<?php

	woopei_header();
			$tabs = array(
				'main'             => __( 'Import/Update Products', 'woo-product-excel-importer' ),
				'exportProducts'   => __( 'Export Products', 'woo-product-excel-importer' ),
				'importCategories' => __( 'Import Categories', 'woo-product-excel-importer' ),
			);

			if ( isset( $_GET['tab'] ) ) {
				$current = wp_unslash( $_GET['tab'] );
			} else {
				$current = 'main';
			}
			echo '<h2 class="nav-tab-wrapper" >';
			foreach ( $tabs as $tab => $name ) {
				$class = ( $tab === $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab". esc_attr( $class ) . "' href='?page=woo-product-importer&tab=". esc_attr( $tab ) . "'>". esc_attr( $name ) . "</a>";
			}
			?>
			<a class='nav-tab premium' href='#'><?php esc_html_e( 'Delete Products', 'woo-product-excel-importer' ); ?></a>
			<a class='nav-tab premium' href='#'><?php esc_html_e( 'Delete Categories', 'woo-product-excel-importer' ); ?></a>
			<a class='nav-tab premium pro' href='#'><?php esc_html_e( 'PRO version', 'woo-product-excel-importer' ); ?></a>
			<a class='nav-tab instructions' href='<?php echo esc_url( plugins_url( '/documentation/documentation.docx', __FILE__ ) ); ?>'><?php esc_html_e( 'Instructions', 'woo-product-excel-importer' ); ?></a>
			<a target='_blank' class=' nav-tab wp_extensions '  style='text-align:center;margin:0 auto' href='https://extend-wp.com'>
				<span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( 'more extensions', 'woo-product-excel-importer' ); ?>
			</a>

			<?php
			echo '</h2>';
			?>

	<?php
	if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'exportProducts' ) {
		$export = new WooexportProducts();
		$export->exportProductsDisplay();
	} elseif ( isset( $_GET['tab'] ) && $_GET['tab'] === 'importCategories' ) {
		$import_cat = new WooImportCategories();
		$import_cat->importCategoriesDisplay();
	} else {
		woopei_main();
	}

	?>

		<div class='get_ajax' style='width:100%;overflow:hidden;' ></div>
		<?php woopei_footer(); ?>
	</div>

	<?php
}

function woopeiPopup() {
	/** this function provides popup for pro version */
	?>
		<div id="woopeiPopup">
			<!-- Modal content -->
			<div class="modal-content">
			<div class='clearfix'><span class="close">&times;</span></div>
			<div class='clearfix verticalAlign'>
				<div class='columns2'>
					<center>
						<img style='width:90%' src='<?php echo esc_url( plugins_url( 'images/woo_product_importer_premium.png', __FILE__ ) ); ?>' style='width:100%' />
					</center>
				</div>

				<div class='columns2'>
					<h3><?php esc_html_e( 'Go PRO and get more important features!', 'woo-product-excel-importer' ); ?></h3>
					<p>&#10004; <?php esc_html_e( 'Import / Update Simple, Variable, Affiliate/External Products with unlimited Attributes + more fields', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Import / Export', 'woo-product-excel-importer' ); ?> <a target='_blank'  href='https://woocommerce.com/products/woocommerce-subscriptions' ><?php esc_html_e( 'Subscription Products', 'woo-product-excel-importer' ); ?></a></p>
					<p>&#10004; <?php esc_html_e( 'Import Product Featured Image and Image Gallery', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Import WPML WooCommerce Product Translations with Excel', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Import / Export ACF custom Product fields and manually defined fields', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Import / Export YOAST SEO Meta Product fields', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Import Product Categories with their Images', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'import gallery images for custom fields like in ACF PRO', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Import Custom Taxonomies along with Products', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Compatible with', 'woo-product-excel-importer' ); ?> <a target='_blank'  href='https://wordpress.org/plugins/woo-variation-swatches/' ><?php esc_html_e( 'Variation Swatches for WooCommerce', 'woo-product-excel-importer' ); ?></a> , <a target='_blank'  href='https://wordpress.org/plugins/woo-variation-gallery/' ><?php esc_html_e( 'Delete Categories', 'woo-product-excel-importer' ); ?><?php esc_html_e( 'Variation Images Gallery', 'woo-product-excel-importer' ); ?></a> , <a target='_blank'  href='https://yithemes.com/themes/plugins/yith-woocommerce-color-and-label-variations/' ><?php esc_html_e( 'YITH WooCommerce Color and Label Variations', 'woo-product-excel-importer' ); ?></a> , <a target='_blank'  href='https://wordpress.org/plugins/perfect-woocommerce-brands/' ><?php esc_html_e( 'Perfect Brands for WooCommerce', 'woo-product-excel-importer' ); ?></a></p>
					<p>&#10004; <?php esc_html_e( 'Save Fields Mapping Template to save Time', 'woo-product-excel-importer' ); ?></p>
					<p>&#10004; <?php esc_html_e( 'Schedule Product Import / Update with Cron Job from excel URL or Google sheets', 'woo-product-excel-importer' ); ?>.</p>
					<p class='bottomToUp'><center><a target='_blank' style='background:#9B2E91;display:block' class='premium_button' href='https://extend-wp.com/product/wordpress-product-import-export-excel-woocommerce/'><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'GET IT HERE', 'woo-product-excel-importer' ); ?></a></center></p>
				</div>
			</div>
			</div>
		</div>
		<?php
}

function woopei_Rating() {
	/** this function provides ability of rating */
	if ( get_option( 'woopei_hide_rating' ) !== 1 ) {
		?>

			<div class="notice notice-success rating ">
				<a href="#" class='dismiss'><span class="dashicons dashicons-dismiss"></span></a>
				<p>
				<strong><?php esc_html_e( 'You like this plugin? ', 'woo-product-excel-importer' ); ?></strong><br/>
			<?php esc_html_e( 'Then please give us a good review ', 'woo-product-excel-importer' ); ?>
					<a target='_blank' href='https://wordpress.org/support/plugin/woo-product-excel-importer/reviews/#new-post'>
						<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( ' here', 'woo-product-excel-importer' ); ?>
					</a>
				</p>
			</div>
		<?php
	}
}

// check more if you like!
add_action( 'wp_ajax_nopriv_woopei_extensions', 'woopei_extensions' );
add_action( 'wp_ajax_woopei_extensions', 'woopei_extensions' );

function woopei_extensions() {
	/** this function provides a popup for extra plugins */
	if ( is_admin() && current_user_can( 'wpeieWoo' ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'woopei_extensions' ) {

		$response = wp_remote_get( 'https://extend-wp.com/wp-json/products/v2/product/category/woocommerce' );

		if ( is_wp_error( $response ) ) {
			return;
		}

		$posts = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $posts ) ) {
			return;
		}

		if ( ! empty( $posts ) ) {

			$allowed_html = array(
				'a'          => array(
					'style' => array(),
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'i'          => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'em'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'strong'     => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'h1'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'h2'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'h3'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'h4'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'h5'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'h6'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'img'        => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'p'          => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'ul'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'li'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'ol'         => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'video'      => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'blockquote' => array(
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'style'      => array(),
				'img'        => array(
					'alt'   => array(),
					'src'   => array(),
					'title' => array(),
					'style' => array(),
					'class' => array(),
					'id'    => array(),
				),
			);

			echo "<div id='woopei_extensions_popup'>";
				echo "<div class='woopei_extensions_content'>";
			?>
						<span class="woopeiclose">&times;</span>
						<h2><i><?php esc_html_e( 'Extend your WordPress functionality with Extend-WP.com well crafted Premium Plugins!', 'woo-product-excel-importer' ); ?></i></h2>
						<hr/>
						<?php
						foreach ( $posts as $post ) {

							echo "<div class='ex_columns'><a target='_blank' href='" . esc_url( $post->url ) . "' /><img src='" . esc_url( $post->image ) . "' /></a>
							<h3><a target='_blank' href='" . esc_url( $post->url ) . "' />" . esc_html( $post->title ) . '</a></h3>
							<div>' . wp_kses( $post->excerpt, $allowed_html ) . "</div>
							<a class='button_extensions button-primary' target='_blank' href='" . esc_url( $post->url ) . "' />" . esc_html__( 'Get it here', 'woo-product-excel-importer' ) . " <i class='fa fa-angle-double-right'></i></a>
							</div>";
						}
						echo '</div>';
						echo '</div>';
		}
		wp_die();
	}
}

// deactivation survey

require plugin_dir_path( __FILE__ ) . '/lib/codecabin/plugin-deactivation-survey/deactivate-feedback-form.php';
add_filter(
	'codecabin_deactivate_feedback_form_plugins',
	function ( $plugins ) {

		$plugins[] = (object) array(
			'slug'    => 'woo-product-excel-importer',
			'version' => '5.9',
		);

		return $plugins;
	}
);

// Email notification form

register_deactivation_hook( __FILE__, 'woopei_deact_hook' );
function woopei_deact_hook() {
	delete_transient( 'woopei_notified' );
}

add_action( 'admin_notices', 'woopei_notification' );

function woopei_notification() {
	/** this function provides ability to subscribe */
	$screen = get_current_screen();
	if ( 'toplevel_page_woo-product-importer' !== $screen->base ) {
		return;
	}

	/* Check transient, if available display notice */
	if ( get_transient( 'woopei_notification' ) ) {
		?>
		<div class="updated notice  woopei_notification">
			<a href="#" class='dismiss' style='float:right;padding:4px' >close</a>

			<h4><i>Product Import Export | <?php esc_html_e( 'Add your Email below & get ', 'woo-product-excel-importer' ); ?><strong>discounts</strong><?php esc_html_e( ' in our pro plugins at', 'woo-product-excel-importer' ); ?> <a href='https://extend-wp.com' target='_blank' >extend-wp.com!</a></i></h4>
			<form method='post' id='woopei_signup'>
				<p>
				<input required type='email' name='woopei_email' />
				<input required type='hidden' name='product' value='218' />
				<input type='submit' class='button button-primary' name='submit' value='<?php esc_html_e( 'Sign up', 'woo-product-excel-importer' ); ?>' />
				</p>
			</form>
		</div>
		<?php

	}
}
add_action( 'wp_ajax_nopriv_woopei_push_not', 'woopei_push_not' );
add_action( 'wp_ajax_woopei_push_not', 'woopei_push_not' );

function woopei_push_not() {

	delete_transient( 'woopei_notification' );
	set_transient( 'woopei_notified', true );
}

add_action( 'wp_ajax_nopriv_woopei_hide_rating', 'woopei_hide_rating' );
add_action( 'wp_ajax_woopei_hide_rating', 'woopei_hide_rating' );

function woopei_hide_rating() {

	update_option( 'woopei_hide_rating', 1 );
}
