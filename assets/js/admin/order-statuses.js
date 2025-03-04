/* global postfinancecheckoutOrderStatusesLocalizeScript, ajaxurl */
( function( $, data, wp, ajaxurl ) {
	$( function() {
		var $tbody = $('.postfinancecheckout-order-statuses-rows'),
			$save_button = $('.postfinancecheckout-order-status-save');

		// Check if wp.template and templates exist before using them.
		var templatesExist = function(templateId) {
			return typeof wp.template === 'function' && document.getElementById('tmpl-' + templateId);
		};

		var $row_template = templatesExist('postfinancecheckout-order-statuses-row') ? wp.template('postfinancecheckout-order-statuses-row') : null,
			$blank_template = templatesExist('postfinancecheckout-order-statuses-row-blank') ? wp.template('postfinancecheckout-order-statuses-row-blank') : null;

		if (!$row_template || !$blank_template) {
			console.warn('Order statuses templates are not present in the DOM');
			return; // Exit if templates do not exist.
		}

		// Ensure key field formatting.
		$(document).on('input', 'input[name="key"]', function() {
			var $charCount = $('#charCount');
			var maxLength = 17; // Character limit.
			var currentLength = this.value.length;
			var remaining = maxLength - this.value.length;
			
			// Ensures that only lower case characters and no blank spaces are allowed.
			this.value = this.value.toLowerCase().replace(/\s+/g, '-').trim();

			// If the text is longer than allowed, cut it down.
			if (currentLength > maxLength) {
				this.value = this.value.substring(0, maxLength);
				remaining = 0;
			}

			// Updates the remaining character counter.
			if (currentLength === 0) {
				$charCount.hide();
			} else {
				$charCount.show().text(remaining + ' ' + data.strings.characters_remaining);
			}
		});
		
		// Blocks entry of more characters when the limit is reached.
		$(document).on('keydown', 'input[name="key"]', function(event) {
			var maxLength = 17;
			if (this.value.length >= maxLength && event.key !== "Backspace" && event.key !== "Delete" && event.key.length === 1) {
				event.preventDefault();
			}
		});

		// Backbone model.
		var OrderStatus = Backbone.Model.extend({
			save: function( changes ) {
				$.post( ajaxurl, {
					action : 'postfinancecheckout_custom_order_status_save_changes',
					postfinancecheckout_order_statuses_nonce : data.postfinancecheckout_order_statuses_nonce,
					changes,
				}, this.onSaveResponse, 'json' );
			},
			onSaveResponse: function( response, textStatus ) {
				if ( 'success' === textStatus ) {
					if ( response.success ) {
						// Get the new status data from the AJAX response.
						var newStatus = response.data.order_status;

						// Add the new status to the beginning of the statuses array.
						var currentStatuses = orderStatus.get( 'statuses' );
						currentStatuses.unshift(newStatus); // Add new item at the start of the array.

						// Update the model with the new array.
						orderStatus.set( 'statuses', currentStatuses );

						// Trigger re-rendering to show the new row.
						orderStatus.trigger( 'saved:statuses' );
					} else if ( response.data ) {
						window.alert( response.data );
					} else {
						window.alert( data.strings.save_failed );
					}
				}
				orderStatusView.unblock();
			}
		} );

		// Backbone view
		var OrderStatusView = Backbone.View.extend({
			rowTemplate: $row_template,
			initialize: function() {
				this.listenTo( this.model, 'saved:statuses', this.render );
				$( document.body ).on( 'click', '.postfinancecheckout-order-status-add-new', { view: this }, this.configureNewOrderStatus );
				$( document.body ).on( 'wc_backbone_modal_response', { view: this }, this.onConfigureOrderStatusSubmitted );
				$( document.body ).on( 'wc_backbone_modal_loaded', { view: this }, this.onLoadBackboneModal );
				$( document.body ).on( 'wc_backbone_modal_validation', this.validateFormArguments );
			},
			block: function() {
				$( this.el ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
			},
			unblock: function() {
				$( this.el ).unblock();
			},
			render: function() {
				var statuses = _.indexBy( this.model.get( 'statuses' ), 'key' ),
					view    = this;

				this.$el.empty();
				this.unblock();

				if ( _.size( statuses ) ) {
					// Sort statuses.
					statuses = _.sortBy( statuses, function( status ) {
						return status.key;
					} );

					// Populate $tbody with the current statuses.
					$.each( statuses, function( id, rowData ) {
						view.renderRow( rowData );
					} );
				} else {
					view.$el.append( $blank_template );
				}
			},
			renderRow: function( rowData ) {
				var view = this;
				view.$el.append( view.rowTemplate( rowData ) );
				view.initRow( rowData );
			},
			initRow: function( rowData ) {
				var view = this;
				var $tr = view.$el.find( 'tr[data-id="' + rowData.key + '"]');

				// Support select boxes.
				$tr.find( 'select' ).each( function() {
					var attribute = $( this ).data( 'attribute' );
					$( this ).find( 'option[value="' + rowData[ attribute ] + '"]' ).prop( 'selected', true );
				} );

				// Check if buttons have data-type="core" and hide them
				var $actionsContainer = $tr.find('.actions-container');
				var options = $actionsContainer.data('type');

				// Hide buttons if 'core'.
				if (options === 'core') {
					$actionsContainer.hide();
				}

				// Make the rows function.
				$tr.find( '.view' ).show();
				$tr.find( '.edit' ).hide();
				$tr.find( '.postfinancecheckout-order-status-edit' ).on( 'click', { view: this }, this.onEditRow );
				$tr.find( '.postfinancecheckout-order-status-delete' ).on( 'click', { view: this }, this.onDeleteRow );
			},
			configureNewOrderStatus: function( event ) {
				event.preventDefault();
				const key = '';

				$( this ).WCBackboneModal({
					template : 'postfinancecheckout-order-statuses-configure',
					variable : {
						key,
						action: 'create',
					},
					data : {
						key,
						action: 'create',
					}
				});
			},
			onConfigureOrderStatusSubmitted: function( event, target, posted_data ) {
				if ( target === 'postfinancecheckout-order-statuses-configure' ) {
					const view = event.data.view;
					const model = view.model;
					const isNewRow = posted_data.key.includes( 'postfinancecheckout_custom_order_status_' );
					const rowData = {
						...posted_data,
					};

					if ( isNewRow ) {
						rowData.newRow = true;
					}
					
					view.block();

					model.save( {
						[ posted_data.key ]: rowData
					} );
				}
			},
			validateFormArguments: function( element, target, data ) {
				const requiredFields = [ 'key' ];
				const formIsComplete = Object.keys( data ).every( key => {
					if ( ! requiredFields.includes( key ) ) {
						return true;
					}
					if ( Array.isArray( data[ key ] ) ) {
						return data[ key ].length && !!data[ key ][ 0 ];
					}
					return !!data[ key ];
				} );
				const createButton = document.getElementById( 'btn-ok' );
				createButton.disabled = ! formIsComplete;
				createButton.classList.toggle( 'disabled', ! formIsComplete );
			},
			onEditRow: function( event ) {
				const key = $( this ).closest('tr').data('id');
				const model =  event.data.view.model;
				const statuses = _.indexBy( model.get( 'statuses' ), 'key' );
				const rowData = statuses[ key ];
				
				event.preventDefault();
				$( this ).WCBackboneModal({
					template : 'postfinancecheckout-order-statuses-configure',
					variable : {
						action: 'edit',
						...rowData
					},
					data : {
						action: 'edit',
						...rowData
					}
				});
			},
			onLoadBackboneModal: function( event, target ) {
				if ( 'postfinancecheckout-order-statuses-configure' === target ) {
					const modalContent = $('.wc-backbone-modal-content');
					const key = modalContent.data('id');
					const model =  event.data.view.model;
					const statuses = _.indexBy( model.get( 'statuses' ), 'key' );
					const rowData = statuses[ key ];

					if ( rowData ) {
						// Support select boxes.
						$('.wc-backbone-modal-content').find( 'select' ).each( function() {
							var attribute = $( this ).data( 'attribute' );
							$( this ).find( 'option[value="' + rowData[ attribute ] + '"]' ).prop( 'selected', true );
						} );
					}
				}
				
			},
			removeRow: function( key ) {
				var $row = this.$el.find('tr[data-id="' + key + '"]');
				if ( $row.length ) {
					$row.fadeOut(300, function() {
						$(this).remove();
					});
				}
			},
			onDeleteRow: function( event ) {
				var view    = event.data.view,
					model   = view.model,
					key = $( this ).closest('tr').data('id');

				event.preventDefault();

				if ( !key ) {
					console.error('No key found for deletion');
					return;
				}

				if ( ! confirm( data.strings.delete_confirmation_msg ) ) {
					return;
				}

				view.block();

				$.post( ajaxurl, {
					action: 'postfinancecheckout_custom_order_status_delete',
					postfinancecheckout_order_statuses_nonce: data.postfinancecheckout_order_statuses_nonce,
					key: key
				}, function( response ) {
					if ( response.success ) {
						// Remove the deleted item from the model.
						view.removeRow(key);

					} else {
						alert( response.data.message || 'Error deleting status' );
					}
				}, 'json' )
				.fail(function(xhr, status, error) {
					view.showErrorModal(xhr.responseJSON?.data?.message);
				})
				.always(function() {
					view.unblock();
				});
			},
		} );
		var orderStatus = new OrderStatus({
			statuses: data.statuses
		} );
		var orderStatusView = new OrderStatusView({
			model:    orderStatus,
			el:       $tbody
		} );

		orderStatusView.render();

		// Function to show error modal
		OrderStatusView.prototype.showErrorModal = function(message) {
			if ( ! $.fn.WCBackboneModal ) {
				alert(message); // Fallback in case WCBackboneModal is not available
				return;
			}

			var modalHtml = `
				<div id="postfinancecheckout-error-modal" class="wc-backbone-modal wc-modal-shipping-zone-method-settings" style="max-width: 400px; margin: auto;">
					<div class="wc-backbone-modal-content">
						<section class="wc-backbone-modal-main" role="main">
							<header class="wc-backbone-modal-header">
								<h1>${"Error"}</h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text">Close</span>
								</button>
							</header>
							<article>
								<p>${message}</p>
							</article>
							<footer>
								<button class="button button-primary modal-close">OK</button>
							</footer>
						</section>
					</div>
					<div class="wc-backbone-modal-backdrop modal-close"></div>
				</div>`;

			$('body').append(modalHtml);
			
			// Show modal using WooCommerce's WCBackboneModal
			$('#postfinancecheckout-error-modal').WCBackboneModal();

			// Remove modal from DOM when closed
			$(document).on('click', '.wc-backbone-modal .modal-close', function() {
				$('#postfinancecheckout-error-modal').remove();
			});
		};
	});
})( jQuery, postfinancecheckoutOrderStatusesLocalizeScript, wp, ajaxurl );
