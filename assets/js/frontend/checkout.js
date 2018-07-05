/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
jQuery(function($) {

    var wc_postfinancecheckout_checkout = {
		payment_methods : {},
		validated : false,
		update_sent : false,
		form_data : '',
		form_data_timer : null,
		checkout_form_identifier : 'form.checkout',
	
		init : function() {
		    var parent = this;
		    // Payment methods
		    $('form.checkout').off('click.woo-postfinancecheckout').on('click.woo-postfinancecheckout',
			    'input[name="payment_method"]', {
				self : this
			    }, this.payment_method_click);
	
		    if ($(document.body).hasClass('woocommerce-order-pay')) {
		    	this.checkout_form_identifier = '#order_review';
				$('#order_review').off('click.woo-postfinancecheckout').on('click.woo-postfinancecheckout',
					'input[name="payment_method"]', {
					    self : this
					}, this.payment_method_click);
		    }
		    this.register_ajax_prefilter();
		    this.form_data_timer = setInterval(this.check_form_data_change.bind(this), 4000);
		    $(this.checkout_form_identifier).find('input[name="payment_method"]:checked').trigger("click");
		    window.wc_postfinancecheckout_checkout = this;
		},
		
		check_form_data_change : function() {
		    var $required_inputs =  $(this.checkout_form_identifier).find( '.address-field.validate-required' ).find('input, select');
		    var current = '';
		    if ( $required_inputs.length ) {
				$required_inputs.each( function() {
					if (! $( this ).is(':visible')) {
					    	return;
					}
					current += $(this).attr('name') +"="+$(this).val()+"&";
				});
				old = this.form_data;
				this.form_data = current;
				if(current != old && !this.update_sent){
				    $required_inputs.filter('.input-text').first().trigger('keydown');
				    this.handle_description_for_empty_iframe(this.get_selected_payment_method());
				}
				this.update_sent = false;
		    }
		    
		},
	
		payment_method_click : function(event) {
		    var self = event.data.self;
		    var current_method = self.get_selected_payment_method();
		    if (!self.is_supported_method(current_method)) {
		    	return;
		    }
		    var configuration = $("#postfinancecheckout-method-configuration-"
			    + current_method);
		    self.register_method(configuration.data("method-id"), configuration
			    .data("configuration-id"), configuration
			    .data("container-id"));
		    self.handle_description_for_empty_iframe(current_method);
		},
	
		handle_description_for_empty_iframe : function(method_id) {
	
		    var current_method = this.get_selected_payment_method();
		    if (!this.is_supported_method(current_method)) {
		    	return;
		    }
		    if (current_method != method_id) {
		    	return;
		    }
		    var configuration = $("#postfinancecheckout-method-configuration-"
			    + current_method);
		    var description = configuration.data("description-available");
	
		    //Hide iFrame by moving it (display:none leads to issues)
		    var item = $(this.checkout_form_identifier).find(
			    'input[name="payment_method"]:checked').closest(
			    'li.wc_payment_method').find('div.payment_box');
		    var form = item.find('#payment-form-' + method_id);
			form.css('display', '');
			
			var required_inputs = $(this.checkout_form_identifier ).find( '.address-field.validate-required:visible' );
			var	has_full_address = true;
	
			if ( required_inputs.length ) {
				required_inputs.each( function() {
					if ( $( this ).find( ':input' ).val() === '' ) {
						has_full_address = false;
						return false;
					}
				});
			}
			
		    if (((!description || description == "false")
			    && this.payment_methods[method_id]['height'] == 0) || !has_full_address) {
				item.css('position', 'absolute');
				item.css('left', '-100000px');
		    } else if ((this.payment_methods[method_id]['height'] == 0) || !has_full_address) {
				item.find("p").css("margin-bottom", 0);
				form.css('position', 'absolute');
				form.css('left', '-100000px');
	
		    } else {
				item.find("p").css("margin-bottom", "");
				item.css('position', '');
				item.css('left', '');
				form.css('position', '');
				form.css('left', '');
		    }
		},
	
		register_ajax_prefilter : function() {
		    var self = this;
		    $.ajaxPrefilter(
			    "json",
			    function(options, originalOptions, jqXHR) {
					if (options.url == wc_checkout_params.checkout_url
						&& self.is_supported_method(self.get_selected_payment_method())) {
					    var original_success = options.success;
					    options.success = function(data, textStatus, jqXHR) {
							if (self.process_order_created(data, textStatus, jqXHR)) {
							    return;
							}
							if (typeof original_success == 'function') {
							    original_success(data, textStatus,jqXHR);
							} else if (typeof original_success != "undefined"
									&& original_success.constructor === Array) {
			        			    	original_success.forEach(function(original_function) {
			        					if (typeof original_function == 'function') {
			        					    original_function(data, textStatus, jqXHR);
			        					}
			        				});
							}
					    };
					}
			   });
		    $.ajaxPrefilter(
			    function(options, originalOptions, jqXHR) {
				var target = wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'update_order_review' );
				if (options.url == target){
				    self.update_sent = true;
				}
			});
	
		},
	
		is_supported_method : function(method_id) {
		    return method_id && (method_id.indexOf('postfinancecheckout_') == 0);
		},
	
		get_selected_payment_method : function() {
		    return $(this.checkout_form_identifier).find(
			    'input[name="payment_method"]:checked').val();
		},
	
		register_method : function(method_id, configuration_id, container_id) {
	
		    if (typeof this.payment_methods[method_id] != 'undefined'
			    && $('#' + container_id).find("iframe").length > 0) {
		    	return;
		    }
		    var self = this;
		    
		    $(this.checkout_form_identifier).block({
				message : null,
				overlayCSS : {
				    background : '#fff',
				    opacity : 0.6
				}
		    });	
		    
		    //Create visible div and add iframe to it. otherwise some browsers have issues reporting the correct height
		    var tmp_container_id = 'tmp-'+container_id;
		    $('<div>').attr('id', tmp_container_id).appendTo('body');
	
		    this.payment_methods[method_id] = {
				configuration_id : configuration_id,
				container_id : tmp_container_id,
				handler : window.IframeCheckoutHandler(configuration_id),
				height : 0
		    };
		    this.payment_methods[method_id].handler
			    .setValidationCallback(function(validation_result) {
				self.process_validation(method_id, validation_result);
			    });
		    this.payment_methods[method_id].handler
			    .setHeightChangeCallback(function(height) {
				self.payment_methods[method_id]['height'] = height;
				self.handle_description_for_empty_iframe(method_id)
			    });
		    
		    this.payment_methods[method_id].handler.setInitializeCallback(function(){
		    	$(self.checkout_form_identifier).unblock();
		    });
	
		    this.payment_methods[method_id].handler
			    .create(self.payment_methods[method_id].container_id);
		    
		    $('#'+container_id).replaceWith($('#'+tmp_container_id));
		    $('#'+tmp_container_id).attr('id', container_id);
		    this.payment_methods[method_id].container_id = container_id;
		    
		    if(this.checkout_form_identifier == '#order_review'){
		    	$(this.checkout_form_identifier).off('submit.postfinancecheckout').on(
				    'submit.postfinancecheckout', function(){
				    	var method_id = self.get_selected_payment_method();
				    	return self.process_submit(method_id);
				    });
		    }
			else{
			 	var form = $(this.checkout_form_identifier);
			    form.off('checkout_place_order_' + method_id + '.postfinancecheckout').on(
				    'checkout_place_order_' + method_id + '.postfinancecheckout', function(){return self.process_submit(method_id);});
			}
		},
		
		process_submit : function(method_id){
			var form = $(this.checkout_form_identifier);
			var required_inputs = $(this.checkout_form_identifier ).find( '.address-field.validate-required:visible' );
			var	has_full_address = true;

			if ( required_inputs.length ) {
				required_inputs.each( function() {
					if ( $( this ).find( ':input' ).val() === '' ) {
						has_full_address = false;
						return false;
					}
				});
			}
			if(!has_full_address){
				$(this.checkout_form_identifier).trigger('validate');
				this.submit_error(postfinancecheckout_js_params.i18n_not_complete);
				return false;
			}
	    	
			clearInterval(this.form_data_timer);
			if (!this.validated) {
			    form.block({
					message : null,
					overlayCSS : {
					    background : '#fff',
					    opacity : 0.6
					}
			    });		
			    this.payment_methods[method_id].handler.validate();
			    return false;
			} else {
				 if(this.checkout_form_identifier == '#order_review'){
					 
					 self = this;
					 $.ajax({
							type:		'POST',
							url:		window.location.href,
							data:		form.serialize(),
							dataType:   'json',
							success:	function( result ) {
								self.validated = false;
							    if (typeof result.postfinancecheckout == 'undefined') {
							    	window.location.reload();
							    	return;
							    }
							    self.payment_methods[self.get_selected_payment_method()].handler
								    .submit();
							    return true;			
							},
							error:	function( jqXHR, textStatus, errorThrown ) {
								window.location.reload();
							}
						});
					 return false;
				 }
			    return true;
			}
		},
	
		process_order_created : function(data, textStatus, jqXHR) {
		    this.validated = false;
		    if (typeof data.postfinancecheckout == 'undefined') {
		    	return false;
		    }
		    this.payment_methods[this.get_selected_payment_method()].handler
			    .submit();
		    return true;
		},
	
		process_validation : function(method_id, validation_result) {
		    if (validation_result.success) {
				this.validated = true;
				$(this.checkout_form_identifier).submit();
				return;
		    } else {
				var form = $(this.checkout_form_identifier);
				form.unblock();
				this.form_data_timer = setInterval(this.check_form_data_change.bind(this),3000);
				if (validation_result.errors) {
				    this.submit_error(validation_result.errors);
				} else {
				    var container_id_selector = $('#'+ this.payment_methods[method_id].container_id);
				    if (container_id_selector.length) {
	        		    $('html, body').animate(
	        			    {
	        				scrollTop : (container_id_selector.offset().top - 20)
	        			    }, 1000);
				    }
				}
		    }
		},
		
		//We emulate the woocommerce submit_error function, as it is not callable from outside
		submit_error: function( error_message ) {
		    	var formatted_message = '<div class="woocommerce-error">'+this.format_error_messages(error_message)+'</div>';
		 	$( '.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove();
			$(this.checkout_form_identifier).prepend( '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + formatted_message + '</div>' );
			$(this.checkout_form_identifier).removeClass( 'processing' ).unblock();
			$(this.checkout_form_identifier).find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).blur();
			$( 'html, body' ).animate({
				scrollTop: ( $(this.checkout_form_identifier ).offset().top - 100 )
			}, 1000 );
			$( document.body ).trigger( 'checkout_error' );
		},
	
		format_error_messages : function(messages) {
		    var formatted_message;
		    if (typeof messages == 'object') {
		    	formatted_message = messages.join("\n");
		    } else if (messages.constructor === Array) {
		    	formatted_message = messages.join("\n").stripTags().toString();
		    } else {
		    	formatted_message = messages
		    }
		    return formatted_message;
		},
    }

    wc_postfinancecheckout_checkout.init();

});