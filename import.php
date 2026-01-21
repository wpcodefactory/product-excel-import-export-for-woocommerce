<?php
/**
 * Product Excel Import & Export for WooCommerce - Import
 *
 * @version 7.0.4
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'PhpOffice\PhpSpreadsheet\IOFactory' ) ) {
	include plugin_dir_path( __FILE__ ) . '/Classes/autoload.php';
}

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * woopei_importProducts.
 *
 * @version 7.0.4
 */
function woopei_importProducts() {

	if (
		isset( $_SERVER['REQUEST_METHOD'] ) &&
		'POST' === $_SERVER['REQUEST_METHOD'] &&
		current_user_can( 'wpeieWoo' ) &&
		isset( $_POST['importProducts'] )
	) {

		check_admin_referer( 'excel_upload' );
		check_ajax_referer( 'excel_upload' );

		if ( isset( $_FILES['file']['tmp_name'] ) ) {
			$filename = $_FILES['file']['tmp_name'] ;
		}

		if (
			isset( $_FILES['file']['size'] ) &&
			$_FILES['file']['size'] > 0
		) {

			if (
				isset( $_FILES['file']['type'] ) &&
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' === $_FILES['file']['type']
			) {

				$objPHPExcel = IOFactory::load( $filename );

				$sheet  = $objPHPExcel->getActiveSheet();
				$total  = $sheet->getHighestDataRow(); // ignores empty rows.
				$totals = max( 0, $total - 1 );

				$rownumber    = 1;
				$row          = $objPHPExcel->getActiveSheet()->getRowIterator( $rownumber )->current();
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells( false );

				?>
				<span class='thisNum'></span>
				<div class='ajaxResponse'></div>

				<form method='POST' id ='product_process' action= "<?php print esc_url( admin_url( 'admin.php?page=woo-product-importer' ) ); ?>">


					<p style='font-style:italic'><?php esc_html_e( ' DATA MAPPING: Drag and drop excel columns on the right to product properties on the left, OR ', 'woo-product-excel-importer' ); ?><i><b> <?php esc_html_e( 'Auto Match Columns', 'woo-product-excel-importer' ); ?> <input type='checkbox' name='automatch_columns' id='automatch_columns' value='yes'  /> </b></i></p>


				<?php
					print "<div style='float:right;width:50%'>";
					print '<h3>' . esc_html__( 'EXCEL COLUMNS', 'woo-product-excel-importer' ) . '</h3><p>';
				foreach ( $cellIterator as $cell ) {
					if ( ! empty( $cell->getValue() ) ) {
						echo "<input type='button' class='draggable' style='min-width:200px' key ='" . esc_attr( $cell->getColumn() ) . "' value='" . esc_attr( $cell->getValue() ) . "' />  <br/>";
					}
				}
				print '</p></div>';
				print "<div style='float:left;width:50%'>";

				?>
				<p class='hideOnUpdateById'>
					<input type='checkbox' name='selectparent' id='selectparent' value='yes'  /> <b> <?php esc_html_e( 'Select Parent Categories as well ', 'woo-product-excel-importer' ); ?></b>
				</p>

				<?php
					print '<h3>' . esc_html__( 'PRODUCT FIELDS', 'woo-product-excel-importer' ) . '</h3>';

					echo '<p>' . esc_html__( 'POST AUTHOR', 'woo-product-excel-importer' ) . " <input type='text' name='post_author' required readonly class='droppable' placeholder='Drop here column' /></p>";
					echo '<p>' . esc_html__( 'POST NAME', 'woo-product-excel-importer' ) . " <input type='text' name='post_name' required readonly class='droppable' placeholder='Drop here column' /></p>";

					echo '<p>' . esc_html__( 'POST TITLE', 'woo-product-excel-importer' ) . " <input type='text' name='post_title' required readonly class='droppable' placeholder='Drop here column' /></p>";
					echo '<p>' . esc_html__( 'POST STATUS', 'woo-product-excel-importer' ) . " <input type='text' name='post_status' required readonly class='droppable' placeholder='Drop here column' /></p>";
					echo '<p>' . esc_html__( 'POST CONTENT', 'woo-product-excel-importer' ) . " <input type='text' name='post_content' required readonly class='droppable' placeholder='Drop here column'  /></p>";
					echo '<p>' . esc_html__( 'POST EXCERPT', 'woo-product-excel-importer' ) . " <input type='text' name='post_excerpt' required readonly class='droppable' placeholder='Drop here column'  /></p>";
					$post_meta = array( '_sku', '_weight', '_regular_price', '_sale_price', '_stock' );
				foreach ( $post_meta as $meta ) {
					echo '<p>' . esc_html( strtoupper( str_replace( '_', ' ', $meta  ) ) ) . " <input type='text' style='min-width:200px' name='" . esc_attr( $meta ) . "' required readonly class='droppable' placeholder='Drop here column'  /></p>";
				}
					echo '<p>' . esc_html__( 'IMAGE', 'woo-product-excel-importer' ) . " <input  type='text' name='' style='border:1px solid red;background:#ccc;' readonly  placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'PRODUCT IMAGE GALLERY', 'woo-product-excel-importer' ) . " <input  type='text'  style='border:1px solid red;background:#ccc;' readonly  placeholder='Premium Version Only' placeholder='Drop here column'  /></p>";

					echo '<p>' . esc_html__( 'VIRTUAL', 'woo-product-excel-importer' ) . "<input type='text' style='min-width:200px' name='_virtual' required readonly class='droppable' placeholder='Downloadable Product'  /></p>";
					echo '<p>' . esc_html__( 'DOWNLOADABLE', 'woo-product-excel-importer' ) . " <input style='border:1px solid red;background:#ccc;' type='text' style='min-width:200px' name='' required readonly class='' placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'PURCHASE NOTE', 'woo-product-excel-importer' ) . " <input style='border:1px solid red;background:#ccc;' type='text' style='min-width:200px' name='' required readonly class='' placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'UPSELL IDS', 'woo-product-excel-importer' ) . "<input style='border:1px solid red;background:#ccc;' type='text' style='min-width:200px' name='' required readonly class='' placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'CROSELL IDS', 'woo-product-excel-importer' ) . " <input style='border:1px solid red;background:#ccc;' type='text' style='min-width:200px' name='' required readonly class='' placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'TAXABLE', 'woo-product-excel-importer' ) . " <input style='border:1px solid red;background:#ccc;' type='text' style='min-width:200px' name='' required readonly class='' placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'TAX CLASS', 'woo-product-excel-importer' ) . " <input style='border:1px solid red;background:#ccc;' type='text' style='min-width:200px' name='' required readonly class='' placeholder='Premium Version Only'  /></p>";
					print '<h3>' . esc_html__( 'CATEGORY AND TAGS', 'woo-product-excel-importer' ) . '</h3>';
					$taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
				foreach ( $taxonomy_objects as $voc ) {
					// ADDITION : INCLUDE ONLY PRODUCT CATEGORY AND TAGS NOT CUSTOM TAXONOMIES
					if ( 'product_tag' === $voc->name || 'product_cat' === $voc->name ) {
						echo '<p>' .  esc_attr( strtoupper( str_replace( '_', ' ', $voc->name  ) ) ) . " <input type='text' style='min-width:200px' name='" . esc_attr( $voc->name ) . "' required readonly class='droppable' placeholder='Drop here column' key /></p>";
					}
				}
					echo '<p>' . esc_html__( 'CUSTOM TAXONOMY', 'woo-product-excel-importer' ) . " <input type='text' name='custom_tax' style='border:1px solid red;background:#ccc;' readonly  placeholder='Premium Version Only'  /></p>";
					echo '<p>' . esc_html__( 'ATTRIBUTES', 'woo-product-excel-importer' ) . " <input type='text' name='product_attr' style='border:1px solid red;background:#ccc;' readonly  placeholder='Premium Version Only'  /></p>";
				?>

					<input type='hidden' name='finalupload' value='<?php print esc_attr( $totals ); ?>' />
					<input type='hidden' name='start' value='1' />
					<input type='hidden' name='action' value='woopei_process' />
				<?php wp_nonce_field( 'excel_process', 'secNonce' );
				submit_button( esc_html__( 'Upload', 'woo-product-excel-importer' ), 'primary', 'check' );
				print '</div>';
				print '</form></div>';

				move_uploaded_file( $_FILES['file']['tmp_name'], plugin_dir_path( __FILE__ ) . 'import.xlsx' );

			} else {
				print '<h3>' . esc_html__( 'Invalid File:Please Upload Excel File', 'woo-product-excel-importer' ) . '</h3>';
			}
		}
	}
}

/**
 * woopei_process.
 *
 * @version 7.0.4
 */
function woopei_process() {

	if ( isset( $_POST['finalupload'] ) && current_user_can( 'wpeieWoo' ) ) {

		check_admin_referer( 'excel_process', 'secNonce' );
		check_ajax_referer( 'excel_process', 'secNonce' );

		$filename = plugin_dir_path( __FILE__ ) . 'import.xlsx';

		$objPHPExcel    = IOFactory::load( $filename );
		$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray( null, true, true, true );
		$data           = count( $allDataInSheet );  // Here get total count of row in that Excel sheet

		// parameters for running with ajax - no php timeouts
		$i     = absint( $_POST['start'] ?? 1 ) + 1;
		$start = $i - 1;

		$images         = array();
		$gallery_images = array();

		// SANITIZE AND VALIDATE title and description
		$title = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['post_title'] ] );
		if ( ! empty( $allDataInSheet[ $i ][ $_POST['post_content'] ] ) ) {
			$content = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['post_content'] ] );
		} else {
			$content = '';
		}

		if ( ! empty( $allDataInSheet[ $i ][ $_POST['post_excerpt'] ] ) ) {
			$excerpt = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['post_excerpt'] ] );
		} else {
			$excerpt = '';
		}

		if ( ! empty( $allDataInSheet[ $i ][ $_POST['post_excerpt'] ] ) ) {
			$author = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['post_author'] ] );
		} else {
			$author = '';
		}
		if ( ! empty( $allDataInSheet[ $i ][ $_POST['post_name'] ] ) ) {
			$url = sanitize_title_with_dashes( $allDataInSheet[ $i ][ $_POST['post_name'] ] );
		} else {
			$url = '';
		}

		if ( ! empty( $allDataInSheet[ $i ][ $_POST['post_status'] ] ) ) {
			$post_status = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['post_status'] ] );
		} else {
			$post_status = '';
		}

		// check if post exists
		if ( post_exists( $title ) === 0 ) {
			$post = array(
				'post_author'  => $author,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => $post_status,
				'post_excerpt' => $excerpt,
				'post_name'    => $url,
				'post_type'    => 'product',
			);
			$id   = wp_insert_post( $post );
			print "<p class='success'><a href='" . esc_url( get_permalink( $id ) ) . "' target='_blank'>" . esc_html( $title ) . '</a> ' . esc_html__( 'created', 'woo-product-excel-importer' ) . '.</p>';
		} else {
			// update
			$id = post_exists( $title );
			if ( $content !== '' ) { // if column selected update, otherwise dont update
				$post = array(
					'ID'           => $id,
					'post_author'  => $author,
					'post_title'   => $title,
					'post_content' => $content,
					'post_status'  => $post_status,
					'post_excerpt' => $excerpt,
					'post_name'    => $url,
					'post_type'    => 'product',
				);
			} else {
				$post = array(
					'ID'           => $id,
					'post_author'  => $author,
					'post_title'   => $title,
					'post_name'    => $url,
					'post_status'  => $post_status,
					'post_excerpt' => $excerpt,
					'post_type'    => 'product',
				);
			}
									wp_update_post( $post );
									print "<p class='warning'><a href='" . esc_url( get_permalink( $id ) ) . "' target='_blank'>" . esc_html( $title ) . '</a> ' . esc_html__( 'already exists. Updated', 'woo-product-excel-importer' ) . '.</p>';
		}

		// IMPORT - UPDATE POST META

					// SANITIZE AND VALIDATE meta data
		if ( isset( $allDataInSheet[ $i ][ $_POST['_sale_price'] ] ) ) {
			$sale_price = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['_sale_price'] ] );
			if ( is_numeric( $sale_price ) && $sale_price >= 0 ) {
				update_post_meta( $id, '_sale_price', $sale_price );
				if ( $sale_price === 0 ) {
					update_post_meta( $id, '_sale_price', '' );
				}
			} else {
				$sale_price = '';
				print esc_html__( 'For sale price of', 'woo-product-excel-importer' ) . esc_html( $title ) . esc_html__( 'you need numbers entered', 'woo-product-excel-importer' ) . '<br/>';

			}
		}

		if ( isset( $allDataInSheet[ $i ][ $_POST['_regular_price'] ] ) ) {
			$regular_price = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['_regular_price'] ] );

			if ( is_numeric( $regular_price ) && $regular_price > 0 ) {
				// if ( $sale_price  && !empty($allDataInSheet[$i][$_POST['_sale_price']]) ) {
				update_post_meta( $id, '_regular_price', $regular_price );
			} else {
				$regular_price = '';
				print esc_html__( 'For regular price of', 'woo-product-excel-importer' ) . esc_html( $title ) . esc_html__( 'you need numbers entered', 'woo-product-excel-importer' ) . '<br/>';

			}
		}

					// ADDITION : IF SALE PRICE IS EMPTY PRICE WILL BE EQUAL TO REGULAR PRICE
		if ( isset( $allDataInSheet[ $i ][ $_POST['_sale_price'] ] ) ) {
			if ( is_numeric( $sale_price ) && $sale_price != 0 ) {
				update_post_meta( $id, '_price', $sale_price );
			} elseif ( isset( $allDataInSheet[ $i ][ $_POST['_regular_price'] ] ) ) {
				update_post_meta( $id, '_price', $regular_price );
			}
		} elseif ( isset( $allDataInSheet[ $i ][ $_POST['_regular_price'] ] ) ) {
			update_post_meta( $id, '_price', $regular_price );
		}

		if ( isset( $allDataInSheet[ $i ][ $_POST['_sku'] ] ) ) {
			$sku = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['_sku'] ] );
			if ( ! $sku && ! empty( $_POST['_sku'] ) ) {
				$sku = '';
				print esc_html__( 'For sku of', 'woo-product-excel-importer' ) . esc_html( $title ) . esc_html__( 'you need numbers entered', 'woo-product-excel-importer' ) . '<br/>';
			} else {
				update_post_meta( $id, '_sku', $sku );
			}
		}

		if ( isset( $allDataInSheet[ $i ][ $_POST['_weight'] ] ) ) {
			$weight = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['_weight'] ] );
			if ( ! $weight && ! empty( $_POST['_weight'] ) ) {
				$weight = '';
				print esc_html__( 'For weight of', 'woo-product-excel-importer' ) . esc_html( $title ) . esc_html__( 'you need numbers entered', 'woo-product-excel-importer' ) . '<br/>';
			} else {
				update_post_meta( $id, '_weight', $weight );
			}
		}

		if ( isset( $allDataInSheet[ $i ][ $_POST['_stock'] ] ) ) {

			$stock = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['_stock'] ] );
			if ( is_numeric( $stock ) && $stock >= 0 ) {
				update_post_meta( $id, '_stock', $stock );
			} else {
				$stock = '';
				print esc_html__( 'For stock of', 'woo-product-excel-importer' ) . esc_html( $title ) . esc_html__( 'you need numbers entered', 'woo-product-excel-importer' ) . '<br/>';

			}

			if ( is_numeric( $stock ) ) {

				update_post_meta( $id, '_manage_stock', 'yes' );
				if ( $stock >= 0 ) {
					update_post_meta( $id, '_stock_status', 'instock' );

				}
				if ( $stock == 0 ) {
					update_post_meta( $id, '_stock_status', 'outofstock' );
				}
			}
		}

		if ( isset( $allDataInSheet[ $i ][ $_POST['_virtual'] ] ) ) {
			$virtual = sanitize_text_field( $allDataInSheet[ $i ][ $_POST['_virtual'] ] );
			if ( ! $virtual && ! empty( $_POST['_virtual'] ) ) {
				$virtual = '';
			} else {
				update_post_meta( $id, '_virtual', $virtual );
			}
		}

		update_post_meta( $id, '_visibility', 'visible' );

					wc_delete_product_transients( $id );
					// TAXONOMIES

					$taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
		foreach ( $taxonomy_objects as $voc ) {
			if ( $voc->name === 'product_tag' || $voc->name === 'product_cat' ) {
				if ( isset( $allDataInSheet[ $i ][ $_POST[ $voc->name ] ] ) ) {
					$taxToImport = explode( ',', sanitize_text_field( $allDataInSheet[ $i ][ $_POST[ $voc->name ] ] ) );
					foreach ( $taxToImport as $taxonomy ) {
						wp_set_object_terms( $id, $taxonomy, $voc->name, true ); // true is critical to append the values

						// GET ALL ASSIGNED TERMS AND ADD PARENT FOR PRODUCT_CAT TAXONOMY!!!
						if ( isset( $_POST['selectparent'] ) ) {
								$terms = wp_get_post_terms( $id, $voc->name );
							foreach ( $terms as $term ) {
								while ( $term->parent !== 0 && ! has_term( $term->parent, sanitize_text_field( $voc->name ), $post ) ) {
											// move upward until we get to 0 level terms
											wp_set_object_terms( $id, array( $term->parent ), sanitize_text_field( $voc->name ), true );
											$term = get_term( $term->parent, esc_attr( $voc->name ) );
								}
							}
						}
					}
				}
			}
		}// end for each taxonomy

		$product = wc_get_product( $id );
		if ( $product instanceof WC_Product ) {
			if ( get_post_meta( $id, '_stock', true ) !== '' ) {
				$product->set_stock_quantity( get_post_meta( $id, '_stock', true ) );
			}
			$product->set_stock_status( 'instock' );
			if ( get_post_meta( $id, '_stock', true ) === '0' ) {
				$product->set_stock_status( 'outofstock' );
			}
			if ( get_post_meta( $id, '_price', true ) !== '' ) {
				$product->set_price( get_post_meta( $id, '_price', true ) );
			}
			if ( get_post_meta( $id, '_sale_price', true ) !== '' ) {
				$product->set_sale_price( get_post_meta( $id, '_sale_price', true ) );
			}
			if ( get_post_meta( $id, '_regular_price', true ) !== '' ) {
				$product->set_regular_price( get_post_meta( $id, '_regular_price', true ) );
			}
			$product->save();
		}

		$i -= 1;
		$finalUpload = absint( $_POST['finalupload'] );
		if ( $i === $finalUpload ) {

			print "<div class='importMessageSussess'><h2>" . esc_html( $i ) . ' / ' . esc_html( $finalUpload ) . ' ' . esc_html__( '- JOB DONE!', 'woo-product-excel-importer' ) . " <a href='" . esc_url( admin_url( 'edit.php?post_type=product' ) ) . "' target='_blank'><i class='fa fa-eye'></i> " . esc_html__( 'GO VIEW YOUR PRODUCTS!', 'woo-product-excel-importer' ) . '</a></h2></div>';

			update_option( 'woopei_show_rating', 1 );

			if ( false === ( get_transient( 'woopei_notified' ) ) ) {
				set_transient( 'woopei_notification', true );
			}

			wp_delete_file( $filename );

		} else {

			print "<div class='importMessage'>
					<h2>" . esc_html( $i ) . ' / ' . esc_html( $finalUpload ) . ' ' . esc_html__( 'Please dont close this page... Loading...', 'woo-product-excel-importer' ) . "</h2>
						<p>
							<img  src='" . esc_url( plugins_url( 'images/loading.gif', __FILE__ ) ) . "' />
						</p>
				</div>";
		}

		die;
	}
}
