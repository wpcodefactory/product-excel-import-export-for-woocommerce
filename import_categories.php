<?php
/**
 * Product Excel Import & Export for WooCommerce - WooImportCategories Class
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'PhpOffice\PhpSpreadsheet\IOFactory' ) ) {
	require plugin_dir_path( __FILE__ ) . '/Classes/autoload.php';
}

use PhpOffice\PhpSpreadsheet\IOFactory;

class WooImportCategories {

	public $pro_url = 'https://extend-wp.com/product/wordpress-product-import-export-excel-woocommerce/';

	public function importCategoriesDisplay() {

		?>
		<h2>
			<?php esc_html_e( 'IMPORT CATEGORY TERMS ', 'woo-product-excel-importer' ); ?>
			- <a target='_blank' href='<?php echo esc_url( $this->pro_url ); ?>'><?php esc_html_e( 'CATEGORY IMAGES in Pro Version', 'woo-product-excel-importer' ); ?></a>
			- <a target='_blank' href='<?php echo esc_url( $this->pro_url ); ?>'><?php esc_html_e( 'ATTRIBUTE TERMS in Pro Version', 'woo-product-excel-importer' ); ?></a>
		</h2>
		<div>

		<?php woopei_Rating(); ?>

			<ul>
			<li>
				<?php esc_html_e( 'Sample File for importing Categories. Upload it using the form below.', 'woo-product-excel-importer' ); ?>
				<a href='<?php echo esc_url( plugins_url( '/sample_excel/import_categories.xlsx', __FILE__ ) ); ?>'>
				<?php esc_html_e( 'sample', 'woo-product-excel-importer' ); ?></a>


			</li>
			<?php if ( is_plugin_active( 'yith-color-and-label-variations-for-woocommerce/init.php' ) || is_plugin_active( 'yith-color-and-label-variations-for-woocommerce-pro/init.php' ) ) { ?>
			<li>
				<a target='_blank' href='https://yithemes.com/themes/plugins/yith-woocommerce-color-and-label-variations/'><?php esc_html_e( 'YITH woocommerce color and labels variations plugin', 'woo-product-excel-importer' ); ?> <?php esc_html_e( 'supported in', 'woo-product-excel-importer' ); ?> <a target='_blank' href='<?php echo esc_url( $this->pro_url ); ?>'><?php esc_html_e( 'Pro Version ', 'woo-product-excel-importer' ); ?></a>

			</li>
			<?php } ?>
			<?php if ( is_plugin_active( 'woo-variation-swatches/woo-variation-swatches.php' ) || is_plugin_active( 'woo-variation-swatches-pro/woo-variation-swatches-pro.php' ) ) { ?>
			<li>

				<a target='_blank' href='https://wordpress.org/plugins/woo-variation-swatches/'><?php esc_html_e( 'Woo Variation swatches', 'woo-product-excel-importer' ); ?> <?php esc_html_e( 'supported in', 'woo-product-excel-importer' ); ?> <a target='_blank' href='<?php echo esc_url( $this->pro_url ); ?>'><?php esc_html_e( 'Pro Version ', 'woo-product-excel-importer' ); ?></a>
			</li>
			<?php } ?>

			<?php if ( is_plugin_active( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php' ) || is_plugin_active( 'perfect-woocommerce-brands-pro/perfect-woocommerce-brands-pro.php' ) ) { ?>

				<li>

					<a target='_blank' href='https://wordpress.org/plugins/perfect-woocommerce-brands/'><?php esc_html_e( 'Perfect Brands for WooCommerce', 'woo-product-excel-importer' ); ?> <?php esc_html_e( 'supported in', 'woo-product-excel-importer' ); ?> <a target='_blank' href='<?php echo esc_url( $this->pro_url ); ?>'><?php esc_html_e( 'Pro Version ', 'woo-product-excel-importer' ); ?></a>


				</li>

			<?php } ?>
			</ul>
			<form method="post" id='categories_import'  action= "<?php echo esc_url( admin_url( 'admin.php?page=woo-product-importer&tab=importCategories' ) ); ?>">

		<?php

				$args             = array(
					'object_type' => array( 'product' ),
				);
				$taxonomy_objects = get_taxonomies( $args );
				$tax_exclude      = array( 'nav_menu', 'link_category', 'post_format', 'term_language', 'term_translations', 'product_shipping_class', 'post_translations', 'product_type' );

				$tax_use = array( 'product_cat' );
				print "<select required name='vocabularySelect' id='vocabularySelect'>";
				print "<option value='' >Select Taxonomy..</option>";
				foreach ( $taxonomy_objects as $voc ) {
					if ( ! in_array( $voc, $tax_exclude ) ) {
						if ( $voc === 'product_cat' ) {
							?>
								<option selected value='<?php print esc_attr( $voc ); ?>'><?php print esc_attr( $voc ); ?></option>
																	<?php
						} else {
							?>
								<option class='proVersion' disabled value=''><?php print esc_attr( $voc ) . esc_html__( ' - in Pro', 'woo-product-excel-importer' ); ?></option>
																						<?php

						}
					}
				}
				?>
								<?php
								print '</select>';

								?>
								<table class="form-table">
					<tr valign="top">
						<td><?php wp_nonce_field( 'importCategories' ); ?>
						<input type='hidden' id='importCategories' name='importCategories' value='1' />
						<div class="uploader" style="background:url(<?php print esc_url( plugins_url( 'images/default.png', __FILE__ ) ); ?> ) no-repeat left center;" >
							<img src="" class='userSelected'/>
							<input type="file"  required name="file" id='woopeiCatFile'  accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
						</div>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Import Terms', 'woo-product-excel-importer' ), 'primary', 'importTerms' ); ?>
			</form>
			<div class='result'><?php $this->importCategories(); ?></div>
		</div>
		<?php
	}




	public function importCategories() {

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can( 'wpeieWoo' ) && isset( $_REQUEST['importCategories'] ) ) {

			check_admin_referer( 'importCategories' );
			check_ajax_referer( 'importCategories' );
			$filename = $_FILES['file']['tmp_name'];

			if ( isset( $_FILES['file']['size'] ) && $_FILES['file']['size'] > 0 ) {
				if ( isset( $_FILES['file']['type'] ) && $_FILES['file']['type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ) {

					$objPHPExcel = IOFactory::load( $filename );

					$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray( null, true, true, true );
					$data           = count( $allDataInSheet );  // Here get total count of row in that Excel sheet.

					for ( $i = 2;$i <= $data;$i++ ) {
						$taxonomy   = 'product_cat';
						$termParent = sanitize_text_field( $allDataInSheet[ $i ]['D'] );

						$term        = sanitize_text_field( $allDataInSheet[ $i ]['A'] );
						$description = sanitize_text_field( $allDataInSheet[ $i ]['B'] );

						if ( ! empty( $allDataInSheet[ $i ]['C'] ) ) {
							$slug = sanitize_title( $allDataInSheet[ $i ]['C'] );
						} else {
							$slug = sanitize_title( $allDataInSheet[ $i ]['A'] );
						}

						// check if post exists.
						if ( $allDataInSheet[ $i ]['D'] === '' ) {
							if ( term_exists( $slug, $taxonomy ) ) {
								print "<p class='warning'>" . esc_html( $term ) . ' ' . esc_html__( 'exists', 'woo-product-excel-importer' ) . '.</p>';

							} else {
										$id = wp_insert_term(
											$term,
											$taxonomy,
											array(
												'description' => $description,
												'slug' => $slug,
											)
										);

								print "<p class='success'>" . esc_html( $term ) . ' ' . esc_html__( 'imported successfully', 'woo-product-excel-importer' ) . '.</p>';
							}
						} elseif ( term_exists( $termParent, $taxonomy ) ) {

								$id  = term_exists( $termParent, $taxonomy );
								$pid = $id['term_id'];

								// import subterm.
							if ( term_exists( $slug, $taxonomy ) ) {
								print "<p class='warning'>" . esc_html( $term ) . ' ' . esc_html__( 'exists', 'woo-product-excel-importer' ) . '.</p>';

									$id = wp_update_term(
										$term,
										$taxonomy,
										array(
											'description' => $description,
											'slug'        => $slug,
											'parent'      => $pid,
										)
									);

							} else {
								$id = wp_insert_term(
									$term,
									$taxonomy,
									array(
										'description' => $description,
										'slug'        => $slug,
										'parent'      => $pid,
									)
								);

								print "<p class='success'>" . esc_html( $term ) . ' ' . esc_html__( 'imported successfully', 'woo-product-excel-importer' ) . '.</p>';
							}
						} else {

								$id  = wp_insert_term( $termParent, $taxonomy );
								$pid = $id['term_id'];
								print "<p class='success'>" . esc_html( $termParent ) . ' ' . esc_html__( 'imported successfully', 'woo-product-excel-importer' ) . '.</p>';


							if ( term_exists( $slug, $taxonomy ) ) {
								print "<p class='warning'>" . esc_html( $term ) . ' ' . esc_html__( 'exists', 'woo-product-excel-importer' ) . '.</p>';
									$id = wp_update_term(
										$term,
										$taxonomy,
										array(
											'description' => $description,
											'slug'        => $slug,
											'parent'      => $pid,
										)
									);

							} else {
									$id = wp_insert_term(
										$term,
										$taxonomy,
										array(
											'description' => $description,
											'slug'        => $slug,
											'parent'      => $pid,
										)
									);

								print "<p class='success'>" . esc_html( $term ) . ' ' . esc_html__( 'imported successfully', 'woo-product-excel-importer' ) . '.</p>';

								if ( false === ( get_transient( 'woopei_notified' ) ) ) {
									set_transient( 'woopei_notification', true );
								}
							}
						}
					}
				}
			}
		}
	}
}
