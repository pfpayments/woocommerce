/**
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package  PostFinanceCheckout
 * @author   postfinancecheckout AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

jQuery(
	function ($) {

		var wc_postfinancecheckout_management = {

			init : function () {
				this.handle_refund_button();
				this.show_refund_states();
				$( '#woocommerce-order-items' ).off( 'click.woo-postfinancecheckout' );
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.postfinancecheckout-completion-show',
					{
						self : this
					},
					this.show_completion
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.action-postfinancecheckout-completion-cancel',
					{
						self : this
					},
					this.cancel_completion
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.action-postfinancecheckout-completion-execute',
					{
						self : this
					},
					this.execute_completion
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.postfinancecheckout-void-show',
					{
						self : this
					},
					this.show_void
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.action-postfinancecheckout-void-cancel',
					{
						self : this
					},
					this.cancel_void
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.action-postfinancecheckout-void-execute',
					{
						self : this
					},
					this.execute_void
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.postfinancecheckout-update-order',
					{
						self : this
					},
					this.update_order
				);
				$( '#woocommerce-order-items' ).on(
					'click.woo-postfinancecheckout',
					'button.refund-items',
					{
						self : this
					},
					this.restrict_refund_inputs
				);
			},

			handle_refund_button : function () {
				if ($( 'span#postfinancecheckout-remove-refund' ).length > 0) {
					$( 'div.wc-order-bulk-actions' ).find( 'button.refund-items' )
					.remove();
				}
			},

			show_refund_states : function () {
				$( 'tbody#order_refunds div.postfinancecheckout-refund-status' )
				.each(
					function () {
						var id = $( this ).data( 'refund-id' );
						var state = $( this ).data( 'refund-state' );
						var refund_thumb = $(
							'tr.refund[data-order_refund_id="' + id
							+ '"]'
						).find( 'td.thumb' );
						if (state == 'failure') {
							refund_thumb
							.addClass( 'postfinancecheckout-refund-state-failue' );
						} else if (state == 'pending'
						|| state == 'sent'
						|| state == 'created') {
							refund_thumb
							.addClass( 'postfinancecheckout-refund-state-pending' );
						}
					}
				);

			},

			show_completion : function (event) {
				var self = event.data.self;

				$( 'div.refund-actions' ).children().remove();

				$( 'div.wc-order-add-item' ).children(
					'button.postfinancecheckout-completion-button'
				).each(
					function (key, value) {
						$( value ).show();
						$( 'div.refund-actions' ).prepend( $( value ) );
					}
				);

				$( 'div.wc-order-refund-items' )
				.find( 'span.woocommerce-Price-amount' ).closest( 'tr' )
				.remove();
				$( 'div.wc-order-refund-items' ).find( '#refund_reason' ).closest( 'tr' )
				.remove();

				$( 'div.wc-order-refund-items' ).find( '#restock_refunded_items' )
				.replaceWith(
					$( 'div.wc-order-add-item' ).find(
						'#completion_restock_not_completed_items'
					)
				);
				$( 'div.wc-order-refund-items' )
				.find( 'label[for="restock_refunded_items"]' )
				.replaceWith(
					$( 'div.wc-order-add-item' )
					.find(
						'label[for="completion_restock_not_completed_items"]'
					)
				);
				$( 'div.wc-order-refund-items' ).find(
					'#completion_restock_not_completed_items'
				).show();
				$( 'div.wc-order-refund-items' ).find(
					'label[for="completion_restock_not_completed_items"]'
				)
				.show();
				$( 'div.wc-order-refund-items' ).find(
					'#completion_restock_not_completed_items'
				).closest( 'tr' )
				.show();

				$( 'div.wc-order-refund-items' ).find( 'label[for="refund_amount"]' )
				.replaceWith(
					$( 'div.wc-order-add-item' ).find(
						'label[for="refund_amount"]'
					)
				);
				$( 'div.wc-order-refund-items' ).find( 'label[for="refund_amount"]' )
				.show();
				$( 'div.wc-order-refund-items' ).find( '#refund_amount' ).prop(
					'readonly',
					true
				);

				$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' )
				.slideUp();
				$( 'div.wc-order-totals-items' ).slideUp();

				$( 'div.wc-order-refund-items' ).slideDown();
				$( 'div.wc-order-item-bulk-edit' ).remove();
				$( '#woocommerce-order-items' ).find( 'input.quantity' ).each(
					function () {
						$( this ).closest( 'td.quantity' ).find(
							'input.refund_order_item_qty'
						).val(
							$( this ).val()
						);
					}
				)

				$( '#woocommerce-order-items' ).find( 'input.line_total' ).each(
					function () {

						$( this ).closest( 'td.line_cost' ).find(
							'input.refund_line_total'
						).val( $( this ).val() );

						$( this ).closest( 'td.line_cost' ).find(
							'input.refund_line_total'
						).data(
							'postfinancecheckout-initial-amount',
							$( this ).val()
						);

						$( this ).closest( 'td.line_cost' ).find(
							'input.refund_line_total'
						).on(
							"change",
							self.update_taxes_for_line_items
						);
					}
				)
				$( '#woocommerce-order-items' ).find( 'input.line_tax' ).each(
					function () {

						$( this ).closest( 'td.line_tax' ).find(
							'input.refund_line_tax'
						).val( $( this ).val() );
						$( this ).closest( 'td.line_tax' ).find(
							'input.refund_line_tax'
						).data(
							'postfinancecheckout-initial-tax',
							$( this ).val()
						);
					}
				);

				$( '#woocommerce-order-items' ).find( 'input.refund_line_tax' ).prop(
					'readonly',
					true
				);
				$( '#woocommerce-order-items' ).find( 'input.refund_line_tax' ).prop(
					'disabled',
					true
				);

				$( '#woocommerce-order-items' ).find( 'input.refund_order_item_qty' )
				.trigger( 'change' );

				$( '#woocommerce-order-items' ).find( 'input.refund_order_item_qty' )
				.closest( 'div.refund' ).show();
				$( '#woocommerce-order-items' ).find( 'input.wc_input_price' ).closest(
					'div.refund'
				).show();
				return false;
			},

			cancel_completion : function () {
				location.reload();

				return false;
			},

			update_taxes_for_line_items : function () {
				var initial_amount = $( this ).data( 'postfinancecheckout-initial-amount' );
				var current_amount = $( this ).val();
				$( this )
				.closest( 'tr' )
				.find( 'input.refund_line_tax' )
				.each(
					function () {
						var initial_tax = $( this ).data(
							'postfinancecheckout-initial-tax'
						);
						var current_tax = 0;
						if (initial_amount !== 0) {
							current_tax = initial_tax * current_amount
							/ initial_amount;
						}

						var formated = parseFloat(
							accounting
							.formatNumber(
								current_tax,
								woocommerce_admin_meta_boxes.rounding_precision,
								''
							)
						)
						.toString()
						.replace(
							'.',
							woocommerce_admin.mon_decimal_point
						);

						$( this ).val( formated );
					}
				);
			},

			execute_completion : function () {

				$( '#woocommerce-order-items' ).block(
					{
						message : null,
						overlayCSS : {
							background : '#fff',
							opacity : 0.6
						}
					}
				);

				if (window.confirm( postfinancecheckout_admin_js_params.i18n_do_completion )) {

					// Get line item refunds.
					var line_item_qtys = {};
					var line_item_totals = {};
					var line_item_tax_totals = {};
					var refund_amount = $( 'input#refund_amount' ).val();

					$( '.refund input.refund_order_item_qty' ).each(
						function (index, item) {
							if ($( item ).closest( 'tr' ).data( 'order_item_id' )) {
								if (item.value) {
									line_item_qtys[$( item ).closest( 'tr' ).data(
										'order_item_id'
									)] = item.value;
								}
							}
						}
					);
					$( '.refund input.refund_line_total' )
					.each(
						function (index, item) {
							if ($( item ).closest( 'tr' ).data(
								'order_item_id'
							)) {
								line_item_totals[$( item ).closest( 'tr' )
								.data( 'order_item_id' )] = accounting
								.unformat(
									item.value,
									woocommerce_admin.mon_decimal_point
								);
							}
						}
					);
					$( '.refund input.refund_line_tax' )
					.each(
						function (index, item) {
							if ($( item ).closest( 'tr' ).data(
								'order_item_id'
							)) {
								var tax_id = $( item ).data( 'tax_id' );

								if ( ! line_item_tax_totals[$( item )
								.closest( 'tr' ).data(
									'order_item_id'
								)]) {
									line_item_tax_totals[$( item )
										.closest( 'tr' ).data(
											'order_item_id'
										)] = {};
								}

								line_item_tax_totals[$( item ).closest(
									'tr'
								).data( 'order_item_id' )][tax_id] = accounting
								.unformat(
									item.value,
									woocommerce_admin.mon_decimal_point
								);
							}
						}
					);
					var data = {
						action : 'woocommerce_postfinancecheckout_execute_completion',
						order_id : woocommerce_admin_meta_boxes.post_id,
						completion_amount : refund_amount,
						line_item_qtys : JSON.stringify( line_item_qtys, null, '' ),
						line_item_totals : JSON.stringify(
							line_item_totals,
							null,
							''
						),
					line_item_tax_totals : JSON.stringify(
						line_item_tax_totals,
						null,
						''
					),
					restock_not_completed_items : $( '#completion_restock_not_completed_items:checked' ).length ? 'true'
					: 'false',
					security : woocommerce_admin_meta_boxes.order_item_nonce
					};

					$.post(
						woocommerce_admin_meta_boxes.ajax_url,
						data,
						function ( response ) {

							if (true === response.success) {
								window.alert( response.data.message );
								window.location.href = window.location.href;
							} else {
								window.alert( response.data.error );
								$( '#woocommerce-order-items' ).unblock();

							}

						}
					);

					return false;
				} else {
					$( '#woocommerce-order-items' ).unblock();
				}
				return false;
			},

			show_void : function () {

				$( 'div.refund-actions' ).children().remove();

				$( 'div.wc-order-add-item' ).children( 'button.postfinancecheckout-void-button' )
				.each(
					function (key, value) {
						$( value ).show();
						$( 'div.refund-actions' ).prepend( $( value ) );
					}
				);
				$( 'div.wc-order-refund-items' )
				.find( 'span.woocommerce-Price-amount' ).closest( 'tr' )
				.remove();
				$( 'div.wc-order-refund-items' ).find( '#refund_reason' ).closest( 'tr' )
				.remove();

				$( 'div.wc-order-refund-items' ).find( '#restock_refunded_items' )
				.replaceWith(
					$( 'div.wc-order-add-item' ).find(
						'#restock_voided_items'
					)
				);
				$( 'div.wc-order-refund-items' ).find(
					'label[for="restock_refunded_items"]'
				).replaceWith(
					$( 'div.wc-order-add-item' ).find(
						'label[for="restock_voided_items"]'
					)
				);
				$( 'div.wc-order-refund-items' ).find( '#restock_voided_items' ).show();
				$( 'div.wc-order-refund-items' ).find(
					'label[for="restock_voided_items"]'
				).show();
				$( 'div.wc-order-refund-items' ).find( '#restock_voided_items' )
				.closest( 'tr' ).show();

				$( 'div.wc-order-refund-items' ).find( 'input#refund_amount' ).closest(
					'tr'
				).remove();

				$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' )
				.slideUp();
				$( 'div.wc-order-totals-items' ).slideUp();

				$( 'div.wc-order-refund-items' ).slideDown();
				$( 'div.wc-order-item-bulk-edit' ).remove();

				return false;
			},

			cancel_void : function () {
				location.reload();

				return false;
			},

			execute_void : function () {

				$( '#woocommerce-order-items' ).block(
					{
						message : null,
						overlayCSS : {
							background : '#fff',
							opacity : 0.6
						}
					}
				);

				if (window.confirm( postfinancecheckout_admin_js_params.i18n_do_void )) {

					var data = {
						action : 'woocommerce_postfinancecheckout_execute_void',
						order_id : woocommerce_admin_meta_boxes.post_id,
						restock_voided_items : $( '#restock_voided_items:checked' ).length ? 'true'
						: 'false',
						security : woocommerce_admin_meta_boxes.order_item_nonce
					};

					$.post(
						woocommerce_admin_meta_boxes.ajax_url,
						data,
						function ( response ) {

							if (true === response.success) {
								window.alert( response.data.message );
								window.location.href = window.location.href;
							} else {
								window.alert( response.data.error );
								window.location.href = window.location.href;
							}

						}
					);
				} else {
					$( '#woocommerce-order-items' ).unblock();
				}
				return false;
			},

			update_order : function () {

				$( '#woocommerce-order-items' ).block(
					{
						message : null,
						overlayCSS : {
							background : '#fff',
							opacity : 0.6
						}
					}
				);

				var data = {
					action : 'woocommerce_postfinancecheckout_update_order',
					order_id : woocommerce_admin_meta_boxes.post_id,
					security : woocommerce_admin_meta_boxes.order_item_nonce
				};

				$.post(
					woocommerce_admin_meta_boxes.ajax_url,
					data,
					function (response) {

						if (true === response.success) {
							window.location.href = window.location.href;
						} else {
							window.alert( response.data.error );
							window.location.href = window.location.href;
						}

					}
				);
				return false;
			},

			restrict_refund_inputs : function (event) {
				var self = event.data.self;

				$( '#woocommerce-order-items' ).find( 'input.line_total' ).each(
					function () {

						$( this ).closest( 'td.line_cost' ).find(
							'input.refund_line_total'
						).data(
							'postfinancecheckout-initial-amount',
							$( this ).val()
						);

						$( this ).closest( 'td.line_cost' ).find(
							'input.refund_line_total'
						).on(
							"change",
							self.update_taxes_for_line_items
						);

					}
				)
				$( '#woocommerce-order-items' ).find( 'input.line_tax' ).each(
					function () {

						$( this ).closest( 'td.line_tax' ).find(
							'input.refund_line_tax'
						).data(
							'postfinancecheckout-initial-tax',
							$( this ).val()
						);
					}
				)

				$( '#woocommerce-order-items' ).find( 'input.refund_line_tax' ).prop(
					'readonly',
					true
				);

				return false;
			},
		}

		wc_postfinancecheckout_management.init();

	}
);
