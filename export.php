<?php
/**
 * Product Excel Import & Export for WooCommerce - WooexportProducts Class
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

class WooexportProducts {

	public $numberOfRows        = 1;
	public $keyword             = '';
	public $posts_per_page      = '';
	public $sale_price          = '';
	public $regular_price       = '';
	public $price_selector      = '';
	public $sale_price_selector = '';
	public $sku                 = '';
	public $offset              = '';

	public function exportProductsDisplay() {
		?>
		<h2>
			<?php esc_html_e( 'EXPORT SIMPLE PRODUCTS', 'woo-product-excel-importer' ); ?>
		</h2>
		<p>
			<i><?php esc_html_e( 'Important Note: always save the generated export file in xlsx format to a new excel for import use.', 'woo-product-excel-importer' ); ?></i>
		</p>
		<div>
			<div class='result'><?php  $this->exportProductsForm() ; ?></div>
		</div>
		<?php
	}

	public function exportProductsForm() {

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => '1',
			)
		);
		if ( $query->have_posts() ) {
			?>
				<p class='exportToggler button button-secondary warning   btn btn-danger'><i class='fa fa-eye '></i>
					<?php esc_html_e( 'Filter & Fields to Show', 'woo-product-excel-importer' ); ?>
				</p>


				<form name='exp_ProductsForm' id='exp_ProductsForm' method='post' action= "<?php echo esc_url( admin_url( 'admin.php?page=woo-product-importer&tab=exportProducts' ) ); ?>" >
					<table class='wp-list-table widefat fixed table table-bordered'>
						<tr>
							<td class='premium'>
								<?php esc_html_e( 'Choose Taxonomy - PRO VERSION', 'woo-product-excel-importer' ); ?>
							</td>
							<td></td>
						</tr>

						<tr>
							<td>
								<?php esc_html_e( 'Keywords', 'woo-product-excel-importer' ); ?>
							</td>
							<td>
								<input type='text' name='keyword'  id='keyword' placeholder='<?php esc_html_e( 'Search term', 'woo-product-excel-importer' ); ?>'/>
							</td>
							<td></td><td></td>
						</tr>
						<tr>
							<td class='premium'><?php esc_html_e( 'SKU', 'woo-product-excel-importer' ); ?> - <?php esc_html_e( 'PRO Version', 'woo-product-excel-importer' ); ?></td>
							<td class='premium'>
								<input type='text' name='sku' id='sku' disabled placeholder='<?php esc_html_e( 'by SKU - PRO', 'woo-product-excel-importer' ); ?>'/>
							</td>
							<td></td><td></td>
						</tr>
						<tr>
							<td class='premium'>
								<?php esc_html_e( 'Regular Price', 'woo-product-excel-importer' ); ?> - <?php esc_html_e( 'PRO Version', 'woo-product-excel-importer' ); ?>
							</td>
							<td class='premium'>
								<input type='number' name='regular_price' disabled id='regular_price' placeholder='<?php esc_html_e( 'Regular Price - PRO', 'woo-product-excel-importer' ); ?>'/>
							</td>
							<td class='premium'>
								<?php esc_html_e( 'Regular Price Selector', 'woo-product-excel-importer' ); ?> - <?php esc_html_e( 'PRO Version', 'woo-product-excel-importer' ); ?>
							</td>
							<td>
								<select name='price_selector' disabled id='price_selector'>
									<option value=">">></option>
									<option value=">=">>=</option>
									<option value="<="><=</option>
									<option value="<"><</option>
									<option value="==">==</option>
									<option value="!=">!=</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class='premium'>
								<?php esc_html_e( 'Sale Price', 'woo-product-excel-importer' ); ?> - <?php esc_html_e( 'PRO Version', 'woo-product-excel-importer' ); ?>
							</td>
							<td>
								<input type='number' name='sale_price' id='sale_price' disabled placeholder='<?php esc_html_e( 'Sale Price - PRO ', 'woo-product-excel-importer' ); ?>'/>
							</td>

							<td class='premium'>
								<?php esc_html_e( 'Sale Price Selector', 'woo-product-excel-importer' ); ?> - <?php esc_html_e( 'PRO Version', 'woo-product-excel-importer' ); ?>
							</td>
							<td class='premium'>
								<select name='sale_price_selector' disabled  id='sale_price_selector' >
									<option value=">">></option>
									<option value=">=">>=</option>
									<option value="<="><=</option>
									<option value="<"><</option>
									<option value="==">==</option>
									<option value="!=">!=</option>
								</select>
							</td>
						</tr>

						<tr>
							<td>
							<?php esc_html_e( 'Limit Results', 'woo-product-excel-importer' ); ?>
							</td>
							<td>
							<input type='number' min="1" max="100000" style='width:100%;'  name='posts_per_page' id='posts_per_page' placeholder='<?php esc_html_e( 'Number to display..', 'woo-product-excel-importer' ); ?>' />
							</td>
							<input type='hidden' name='offset' style='width:100%;' id='offset' placeholder='<?php esc_html_e( 'Start from..', 'woo-product-excel-importer' ); ?>' />
							<input type='hidden' name='start' /><input type='hidden' name='total' />

							<td></td><td></td>
						</tr>

					</table>

					<?php $taxonomy_objects = array( 'product_cat', 'product_tag' ); ?>

					<table class='wp-list-table widefat fixed table table-bordered'>
						<legend>
							<h2>
								<?php esc_html_e( 'TAXONOMIES TO SHOW', 'woo-product-excel-importer' ); ?> - <span class='premium'><?php esc_html_e( 'More in PRO Version', 'woo-product-excel-importer' ); ?></span>
							</h2>
						</legend>

						<tr>
							<?php
							$cols    = array();
							$checked = 'checked';
							foreach ( $taxonomy_objects as $voc ) {

								print "<td>
								<input type='checkbox' class='fieldsToShow' " . esc_attr( $checked ) . " name='toShow" . esc_attr( $voc ) . "' value='1'/>
								<label for='" . esc_attr( str_replace( '_', ' ',  $voc ) ) . "'>" . esc_attr( str_replace( '_', ' ',  $voc ) ) . '</label>
								</td>';
								array_push( $cols, esc_attr( $voc ) );
							}
							?>
						</tr>
					</table>


					<table class='wp-list-table widefat fixed table table-bordered'>
						<legend>
							<h2>
								<?php esc_html_e( 'FIELDS TO SHOW', 'woo-product-excel-importer' ); ?> - <span class='premium'><?php esc_html_e( 'More in PRO Version', 'woo-product-excel-importer' ); ?></span>
							</h2>
						</legend>
						<?php
						$cols = array( 'title', 'description', 'excerpt', '_sku', '_regular_price', '_sale_price', '_weight', '_stock', '_stock_status', '_width', '_length', '_height', '_virtual' );
						?>

						<tr>

						<?php
						$checked = 'checked';
						foreach ( $cols as $col ) {
							print "<td>
								<input type='checkbox' class='fieldsToShow' checked name='toShow" . esc_attr( $col ) . "' value='1'/>
								<label for='" . esc_html( $col ) . "'>" . esc_html( $col ) . '</label>
								</td>';
						}
						?>

						</tr>
					</table>

					<input type='hidden' name='columnsToShow' value='1'  />
					<input type='hidden' id='action' name='action' value='woopei_exportProducts' />
					<?php wp_nonce_field( 'columnsToShow' ); ?>

					<?php submit_button( esc_html__( 'Search', 'woo-product-excel-importer' ), 'primary', 'Search' ); ?>

				</form>

			<div class='resultExport'>
				<?php $this->exportProducts(); ?>
			</div>
			<?php
		} else {
			print "<h4 class='error'>" . esc_html__( 'There are no products to export....', 'woo-product-excel-importer' ) . '</h4>'; // end of checking for products
		}
	}


	public function exportProducts() {

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can( 'wpeieWoo' ) && isset( $_REQUEST['columnsToShow'] ) ) {

			check_admin_referer( 'columnsToShow' );
			check_ajax_referer( 'columnsToShow' );

			if ( ! empty( $_POST['keyword'] ) ) {
				$this->keyword = sanitize_text_field( wp_unslash( $_POST['keyword'] ) );
			}

			if ( ! empty( $_POST['posts_per_page'] ) ) {
				$this->posts_per_page = (int) $_POST['posts_per_page'];
			} else {
				$this->posts_per_page = '-1';
			}

			if ( ! empty( $_POST['offset'] ) ) {
				$this->offset = (int) $_POST['offset'];
			} else {
				$this->offset = '-1';
			}

			$query = new WP_Query(
				array(
					'post_type'      => 'product',
					's'              => $this->keyword,
					'offset'         => $this->offset,
					'posts_per_page' => $this->posts_per_page,
				)
			);

			if ( $query->have_posts() ) {

				$i = 0;
				?>
				<p class='message error'>
					<?php esc_html_e( 'Wait... Download is loading...', 'woo-product-excel-importer' ); ?>
					<b class='totalPosts' ><?php print esc_html( $query->post_count ); ?></b>
				</p>

				<?php
				if ( $query->post_count <= 500 ) {
					$start = 0;
				} else {
					$start = 500;
				}
				print " <b class='startPosts'>" . esc_html( $start ) . '</b>';
			}

			$arrayIDs = array();

			$column_name = array( esc_html__( 'id', 'woo-product-excel-importer' ), esc_html__( 'TITLE', 'woo-product-excel-importer' ), esc_html__( 'DESCRIPTION', 'woo-product-excel-importer' ), esc_html__( 'EXCERPT', 'woo-product-excel-importer' ), ' ' . esc_html__( 'SKU', 'woo-product-excel-importer' ), ' ' . esc_html__( 'REGULAR PRICE', 'woo-product-excel-importer' ), ' ' . esc_html__( 'SALE PRICE', 'woo-product-excel-importer' ), ' ' . esc_html__( 'WEIGHT', 'woo-product-excel-importer' ), ' ' . esc_html__( 'STOCK', 'woo-product-excel-importer' ), ' ' . esc_html__( 'STOCK STATUS', 'woo-product-excel-importer' ), ' ' . esc_html__( 'WIDTH', 'woo-product-excel-importer' ), ' ' . esc_html__( 'LENGTH', 'woo-product-excel-importer' ), ' ' . esc_html__( 'HEIGHT', 'woo-product-excel-importer' ), ' ' . esc_html__( 'VIRTUAL', 'woo-product-excel-importer' ) );

			$post_meta = array( '_sku', '_regular_price', '_sale_price', '_weight', '_stock', '_stock_status', '_width', '_length', '_height', '_virtual' );

			?>
			<div id="myProgress">
				<div id="myBar"></div>
			</div>

			<div class='exportTableWrapper' style='overflow:auto;width:100%;max-height:600px;'>
				<table id='toExport'>
					<thead>
						<tr>
							<th>
								<?php esc_html_e( 'ID', 'woo-product-excel-importer' ); ?>
							</th>
							<?php
							$taxonomy_objects = array( 'product_cat', 'product_tag' );

							foreach ( $taxonomy_objects as $tax ) {
								if ( isset( $_REQUEST[ 'toShow' . $tax ] ) ) { // show columns according to what is checked
									array_push( $column_name, $tax );
								}
							}

							foreach ( $column_name as $d ) {
								if ( isset( $_REQUEST[ 'toShow' . strtolower( str_replace( ' ', '_', $d ) ) ] ) ) {
									$d = strtoupper( str_replace( '_', ' ', $d ) );
									print '<th>' . esc_html( $d ) . '</th>';
								}
							}
							?>
						</tr>
					</thead>
					<tbody class='tableExportAjax'>
					</tbody>
				</table>
			</div>
			<?php

		}//check request
	}
}



function woopei_exportProducts() {

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can( 'wpeieWoo' ) ) {

		check_admin_referer( 'columnsToShow' );
		check_ajax_referer( 'columnsToShow' );
		$keyword = '';
		if ( ! empty( $_POST['keyword'] ) ) {
			$keyword = sanitize_text_field(  wp_unslash( $_POST['keyword'] ) );
		}

		if ( ! empty( $_POST['posts_per_page'] ) ) {
			$posts_per_page = (int) $_POST['posts_per_page'];
		} else {
			$posts_per_page = '-1';
		}

		if ( ! empty( $_POST['offset'] ) ) {
			$offset = (int) $_POST['offset'];
		} else {
			$offset = '0';
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				's'              => $keyword,
				'posts_per_page' => $posts_per_page,
				'offset'         => $offset,

			)
		);

		if ( $query->have_posts() ) {

			$post_meta = array( '_sku', '_regular_price', '_sale_price', '_weight', '_stock', '_stock_status', '_width', '_length', '_height', '_virtual' );

			while ( $query->have_posts() ) {
				$query->the_post();
				global $product;
				global $woocommerce;

				if ( $product->is_type( 'simple' ) ) {
					?>
									<tr>
						<td><?php print esc_attr( get_the_ID() ); ?></td>
						<?php if ( isset( $_REQUEST['toShowtitle'] ) ) { ?>
							<td><?php esc_attr( the_title() ); ?></td>
						<?php } ?>
						<?php if ( isset( $_REQUEST['toShowdescription'] ) ) { ?>
							<td>
								<?php print esc_attr( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) ); ?>
							</td>
						<?php } ?>
						<?php if ( isset( $_REQUEST['toShowexcerpt'] ) ) { ?>
							<td>
								<?php print esc_attr( wp_strip_all_tags( get_post_field( 'post_excerpt', get_the_ID() ) ) ); ?>
							</td>
						<?php } ?>


						<?php
						foreach ( $post_meta as $meta ) {
							if ( isset( $_REQUEST[ 'toShow' . $meta ] ) ) {

									?>
									<td><?php print esc_attr( get_post_meta( get_the_ID(), $meta, true ) ); ?></td>

									<?php

							}
						}
						$terms = get_post_taxonomies( get_the_ID() );

						foreach ( $terms as $tax ) {
							$term = get_the_terms( get_the_ID(), $tax );
							if ( isset( $_REQUEST[ 'toShow' . $tax ] ) ) {// show columns according to what is checked

								if ( ! empty( $term ) ) {
									$myterm = array();
									foreach ( $term as $t ) {
													array_push( $myterm, $t->name );
									}
												$terms = implode( ',', $myterm );
												print '<td>';
												print esc_html( $terms );
												print '</td>';
								} else {
									print '<td></td>';
								}
							}
						}

						print '</tr>';
				}
			}//end while
			die;

			if ( false === ( get_transient( 'woopei_notified' ) ) ) {
				set_transient( 'woopei_notification', true );
			}
		} else {
			print "<p class='warning' >" . esc_html_e( 'No Product Found', 'woo-product-excel-importer' ) . '</p>';// end if
		}
	}//check request
}
