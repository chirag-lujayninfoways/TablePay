var rpress_scripts;

jQuery(document).ready(function($) {

  // Hide un-necessary elements. These are things that are required in case JS breaks or isn't present
  $('.rpress-no-js').hide();

  //Triggers when the add button is clicked.
  $('.rpress-add-to-cart').click(function(e) {

    e.preventDefault();
    var $this   = $(this);
    var item_id = $this.attr('data-fooditem-id');
    var price   = $this.attr('data-price');

    // if ( typeof Cookies.get('service_type') == 'undefined' ){
    //   rpress_display_service_options( item_id );
    //   return true;
    // }

    var data = {
        action:         'rpress_fooditem_options',
        fooditem_id:    item_id,
        fooditem_price: price,
    };

    $('#rpressModal').removeClass('rpress-delivery-options').addClass( 'rpress-food-options' );
    $.fancybox.open({
      type      : 'html',
      afterShow : function(instance, current) {
        instance.showLoading(current);
      }
    });

    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: rpress_scripts.ajaxurl,
      xhrFields: {
          withCredentials: true
      },

      success: function(response) {

        // console.log(response);

       $.fancybox.close(true);

        $('#rpressModal .modal-title').html(response.data.html_title);
        $('#rpressModal .modal-body').html(response.data.html);
        $('#rpressModal .modal-body').prepend("<div class='fooditem-description'>"+response.data.fooditem_description+"</div>");
        $('#rpressModal .rpress-prices').html(response.data.food_price);

        if (item_id !== '' && price !== '') {
          $('#rpressModal').find('.submit-fooditem-button').attr({
            'data-item-id': item_id,
            'data-item-price': price,
            'data-item-qty': 1,
          });
        }

        $('#rpressModal').find('.submit-fooditem-button')
        .attr('data-cart-action', 'add-cart')
        .removeClass('update-cart')
        .addClass('add-cart')
        .text(rpress_scripts.add_to_cart);

        $('#rpressModal .qty').val(1);
        $('#rpressModal').modal('show');
      }
    });
    return false;
  });

  //Display the service options modal
  function rpress_display_service_options( item_id = '' ){

    var data = {
      action: 'rpress_show_delivery_options'
    };

    $.fancybox.open({
      type      : 'html',
      afterShow : function(instance, current) {
        instance.showLoading(current);
        $( '#rpressModal' ).removeClass('rpress-food-options');
      }
    });

    $.ajax({
      type: 'POST',
      url: rpress_scripts.ajaxurl,
      data: data,
      success: function( response ) {
        if ( ! response ) {
          return;
        }

        $.fancybox.close(true);

        $('#rpressModal .modal-title').html( response.data.html_title );

        $('#rpressModal .modal-body').html( response.data.html );

        $( '#rpressModal' ).addClass( 'rpress-delivery-options' ).modal('show');

        var serviceType = Cookies.get('service_type');
        var serviceTime = Cookies.get('service_time');
        var serviceDate = Cookies.get('service_date');

        if ( typeof serviceType !== 'undefined' ) {
            if( typeof serviceTime !== 'undefined' ){
                $('.rpress-delivery-wrap').find('.rpress-pickup,.rpress-delivery').val(serviceTime);
            }
            $('.rpress-delivery-wrap').find('.rpress-delivery-time-wrap,.rpress-pickup-time-wrap').show();
        }

        if ( serviceDate !== '' || serviceDate != undefined ) {
          $('.rpress-delivery-wrap').find('.rpress_get_delivery_dates').val(serviceDate);
        }

        if( item_id !== '' )
          $('.rpress-delivery-opt-update').attr('data-food-id',item_id);

        //Activate the tab
        if ( $('.rpress-tabs-wrapper').length && typeof serviceType !== 'undefined' ) {
          $('.rpress-delivery-wrap').find('a#nav-' + serviceType + '-tab').trigger('click');
        }
        else if ( $('.rpress-tabs-wrapper').length ) {
          $('#rpressdeliveryTab > li:first-child > a')[0].click();
        }

        // Trigger event so themes can refresh other areas.
        $( document.body ).trigger( 'opened_service_options', [ response.data ] );
      },
      dataType: 'json'
    });
  }

  //Hide delivery error when switch tabs
  $('body').on('click', '.rpress-delivery-options li.nav-item', function(e) {
    e.preventDefault();
    $(this).parents('.rpress-delivery-wrap').find('.rpress-order-time-error').addClass('hide');
  });

  //Update delivery options
  $('body').on('click', '.rpress-delivery-opt-update', function(e) {
    e.preventDefault();

    var $this = $(this);
    var DefaultText = $this.text();
    var FoodItemId = $this.attr('data-food-id');
    var currentService = Cookies.get('service_type');

    if ( $('.rpress-tabs-wrapper').find('.nav-item.active a').length > 0 ) {
      var serviceType   = $('.rpress-tabs-wrapper').find('.nav-item.active a').attr('data-service-type');
      var serviceLabel  = $('.rpress-tabs-wrapper').find('.nav-item.active a').text().trim();
      //Store the service label for later use
      window.localStorage.setItem( 'serviceLabel', serviceLabel );
    }

    var serviceTime = $('.rpress-tabs-wrapper').find('.delivery-settings-wrapper.active .rpress-hrs').val();
    var serviceDate = $('.rpress-tabs-wrapper').find('.delivery-settings-wrapper.active .rpress_get_delivery_dates').val();

    if ( serviceTime === undefined && ( rpress_scripts.pickup_time_enabled == 1 && serviceType == 'pickup'  || rpress_scripts.delivery_time_enabled == 1 && serviceType == 'delivery' )) {
      $this.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').text('Please select time for ' + serviceLabel);
      $this.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('disabled').addClass('enable');
      return false;
    }

    var sDate = serviceDate === undefined ? rpress_scripts.current_date : serviceDate;

    $this.parents('.rpress-delivery-wrap').find('.rpress-errors-wrap').removeClass('enable').addClass('disabled');
    $this.text(rpress_scripts.please_wait);

    var action = 'rp_check_service_slot';
    var data = {
        action: action,
        serviceType: serviceType,
        serviceTime: serviceTime,
        service_date: sDate
    };

    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: rpress_scripts.ajaxurl,
      xhrFields: {
        withCredentials: true
      },
      success: function( response ) {
        if ( response.status == 'error' ) {
          $this.text(rpress_scripts.update);
          $this.parents('#rpressModal').find('.rpress-errors-wrap').addClass('disabled')
          $this.parents('.rpress-delivery-options').find('.rpress-error-msg').remove();
          $this.parents('#rpressModal').find('.rpress-errors-wrap').html(response.msg).removeClass('disabled');
          return false;
        }
        else {
          Cookies.set('service_type', serviceType, { path: '/' });

          if ( serviceDate === undefined ) {
            Cookies.set( 'service_date', rpress_scripts.current_date, { path: '/' });
            Cookies.set( 'delivery_date', rpress_scripts.display_date, { path: '/' });
          }
          else {
            var delivery_date = $('.delivery-settings-wrapper.active .rpress_get_delivery_dates option:selected').text();
            Cookies.set( 'service_date', serviceDate, { path: '/' });
            Cookies.set( 'delivery_date', delivery_date, { path: '/' });
          }

          if( serviceTime === undefined ) {
            Cookies.set( 'service_time', '', { path: '/' } );
          }
          else {
            Cookies.set( 'service_time', serviceTime, { path: '/' });
          }

          if ( FoodItemId ) {
            $('#rpressModal').modal('hide');
            setTimeout(function(){
              $('.rpress-add-to-cart[data-fooditem-id="'+FoodItemId+'"]').trigger('click');
            },300);
          }
          else {
            if ( typeof serviceType !== 'undefined' && typeof serviceTime !== 'undefined' ) {
              $('.delivery-wrap .delivery-opts').html('<span class="delMethod">' + serviceLabel + ',</span> <span class="delTime"> ' + Cookies.get( 'delivery_date' ) + ', ' + serviceTime + '</span>');
            }
            else if( typeof serviceTime == 'undefined' ) {
              $('.delivery-wrap .delivery-opts').html('<span class="delMethod">' + serviceLabel + ',</span> <span class="delTime"> ' + Cookies.get( 'delivery_date' ) + '</span>');
            }
            $('#rpressModal').modal('hide');
          }

          //Trigger checked slot event so that it can be used by theme/plugins
          $( document.body ).trigger( 'rpress_checked_slots', [response] );

          //If it's checkout page then refresh the page to reflect the updated changes.
          if( rpress_scripts.is_checkout == '1' )
            window.location.reload();
        }
      }
    });
  });

  // Show the login form on the checkout page
  $('#rpress_checkout_form_wrap').on('click', '.rpress_checkout_register_login', function() {
      var $this = $(this),
        payment_form = $('#rpress_payment_mode_select_wrap,#rpress_purchase_form_wrap');
        ajax_loader = '<span class="rpress-loading-ajax rpress-loading"></span>';
        data = {
              action: $this.data('action')
        };
        payment_form.hide();
    // Show the ajax loader
    $this.html($this.html() + ajax_loader);

    $.post(rpress_scripts.ajaxurl, data, function(checkout_response) {
      $('#rpress_checkout_login_register').html(rpress_scripts.loading);
      $('#rpress_checkout_login_register').html(checkout_response);
      // Hide the ajax loader
      $('.rpress-cart-ajax').hide();
      //Show the payment form
      if( data.action == 'checkout_register' )
        payment_form.show();
    });
    return false;
  });

  // Process the login form via ajax
  $(document).on('click', '#rpress_purchase_form #rpress_login_fields input[type=submit]', function(e) {

    e.preventDefault();

    var complete_purchase_val = $(this).val();

    $(this).val(rpress_global_vars.purchase_loading);

    $(this).after('<span class="rpress-loading-ajax rpress-loading"></span>');

    var data = {
      action: 'rpress_process_checkout_login',
      rpress_ajax: 1,
      rpress_user_login: $('#rpress_login_fields #rpress_user_login').val(),
      rpress_user_pass: $('#rpress_login_fields #rpress_user_pass').val()
    };

    $.post(rpress_global_vars.ajaxurl, data, function(data) {

      if ( $.trim(data) == 'success' ) {
        $('.rpress_errors').remove();
        window.location = rpress_scripts.checkout_page;
      }
      else {
        $('#rpress_login_fields input[type=submit]').val(complete_purchase_val);
        $('.rpress-loading-ajax').remove();
        $('.rpress_errors').remove();
        $('#rpress-user-login-submit').before(data);
      }
    });

  });

  // Load the fields for the $this payment method
  $('select#rpress-gateway, input.rpress-gateway').change(function(e) {

    var payment_mode = $('#rpress-gateway option:selected, input.rpress-gateway:checked').val();

    if (payment_mode == '0') {
      return false;
    }

    rpress_load_gateway(payment_mode);

    return false;
  });

  // Auto load first payment gateway
  if (rpress_scripts.is_checkout == '1') {

    var chosen_gateway = false;
    var ajax_needed = false;

    if ($('select#rpress-gateway, input.rpress-gateway').length) {
      chosen_gateway = $("meta[name='rpress-chosen-gateway']").attr('content');
      ajax_needed = true;
    }

    if (!chosen_gateway) {
      chosen_gateway = rpress_scripts.default_gateway;
    }

    if ( ajax_needed ) {

      // If we need to ajax in a gateway form, send the requests for the POST.
      setTimeout(function() {
          rpress_load_gateway(chosen_gateway);
      }, 200);

    }
    else {

      // The form is already on page, just trigger that the gateway is loaded so further action can be taken.
      $('body').trigger('rpress_gateway_loaded', [chosen_gateway]);

    }
  }

  //Update service options.
  $( document.body ).on('click', '.delivery-change', function(e) {
    e.preventDefault();
    rpress_display_service_options();
    return true;
  });

  // Process checkout
  $(document).on('click', '#rpress_purchase_form #rpress_purchase_submit [type=submit]', function(e) {

      var rpressPurchaseform = document.getElementById('rpress_purchase_form');

      if (typeof rpressPurchaseform.checkValidity === "function" && false === rpressPurchaseform.checkValidity()) {
          return;
      }

      e.preventDefault();

      var complete_purchase_val = $(this).val();

      $(this).val(rpress_global_vars.purchase_loading);

      $(this).prop('disabled', true);

      $(this).after('<span class="rpress-loading-ajax rpress-loading"></span>');

      $.post(rpress_global_vars.ajaxurl, $('#rpress_purchase_form').serialize() + '&action=rpress_process_checkout&rpress_ajax=true', function(data) {
          if ($.trim(data) == 'success') {
              $('.rpress_errors').remove();
              $('.rpress-error').hide();
              $(rpressPurchaseform).submit();
          } else {
              $('#rpress-purchase-button').val(complete_purchase_val);
              $('.rpress-loading-ajax').remove();
              $('.rpress_errors').remove();
              $('.rpress-error').hide();
              $(rpress_global_vars.checkout_error_anchor).before(data);
              $('#rpress-purchase-button').prop('disabled', false);

              $(document.body).trigger('rpress_checkout_error', [data]);
          }
      });

  });

  // Update state field
  $( document.body ).on( 'change', '#rpress_cc_address input.card_state, #rpress_cc_address select, #rpress_address_country', update_state_field );

  function update_state_field() {

      var $this = $(this);
      var $form;
      var is_checkout = typeof rpress_global_vars !== 'undefined';
      var field_name = 'card_state';
      if ($(this).attr('id') == 'rpress_address_country') {
          field_name = 'rpress_address_state';
      }

      if ('card_state' != $this.attr('id')) {

          // If the country field has changed, we need to update the state/province field
          var postData = {
              action: 'rpress_get_shop_states',
              country: $this.val(),
              field_name: field_name,
          };

          $.ajax({
              type: "POST",
              data: postData,
              url: rpress_scripts.ajaxurl,
              xhrFields: {
                  withCredentials: true
              },
              success: function(response) {
                  if (is_checkout) {
                      $form = $("#rpress_purchase_form");
                  } else {
                      $form = $this.closest("form");
                  }

                  var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="rpress_address_state"], select[name="rpress_address_state"]';

                  if ('nostates' == $.trim(response)) {
                      var text_field = '<input type="text" name="card_state" class="card-state rpress-input required" value=""/>';
                      $form.find(state_inputs).replaceWith(text_field);
                  } else {
                      $form.find(state_inputs).replaceWith(response);
                  }

                  if (is_checkout) {
                      $(document.body).trigger('rpress_cart_billing_address_updated', [response]);
                  }

              }
          }).fail(function(data) {
              if (window.console && window.console.log) {
                  console.log(data);
              }
          }).done(function(data) {
              if (is_checkout) {
                  recalculate_taxes();
              }
          });
      } else {
          if (is_checkout) {
              recalculate_taxes();
          }
      }

      return false;
  }

  // If is_checkout, recalculate sales tax on postalCode change.
  $( document.body ).on( 'change', '#rpress_cc_address input[name=card_zip]', function() {
    if ( typeof rpress_global_vars !== 'undefined' ) {
      recalculate_taxes();
    }
  });

  $("#rpressModal").on('hide.bs.modal', function(){
    $('.modal-backdrop.in').remove();
  });

  // Remove an item from cart.
  $('.rpress-cart').on('click', '.rpress-remove-from-cart', function(event) {
    var $this = $(this),
        item = $this.data( 'cart-item' ),
        action = $this.data( 'action' ),
        id = $this.data( 'fooditem-id' ),
        data = {
          action: action,
          cart_item: item
        };

        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: rpress_scripts.ajaxurl,
          xhrFields: {
            withCredentials: true
          },
          success: function(response) {

            if ( response.removed ) {

              // Remove the $this cart item
              $('.rpress-cart .rpress-cart-item').each(function() {
                $(this).find("[data-cart-item='" + item + "']").parents('.rpress-cart-item').remove();
              });

              // Check to see if the purchase form(s) for this fooditem is present on this page
              if ($('[id^=rpress_purchase_' + id + ']').length) {
                $('[id^=rpress_purchase_' + id + '] .rpress_go_to_checkout').hide();
                $('[id^=rpress_purchase_' + id + '] a.rpress-add-to-cart').show().removeAttr('data-rpress-loading');

                if (rpress_scripts.quantities_enabled == '1') {
                  $('[id^=rpress_purchase_' + id + '] .rpress_fooditem_quantity_wrapper').show();
                }
              }

              $('span.rpress-cart-quantity').text(response.cart_quantity);

              $(document.body).trigger('rpress_quantity_updated', [response.cart_quantity]);

                if ( rpress_scripts.taxes_enabled ) {
                  $('.cart_item.rpress_subtotal span').html(response.subtotal);
                  $('.cart_item.rpress_cart_tax span').html(response.tax);
                }

                $('.cart_item.rpress_total span.rpress-cart-quantity').html(response.cart_quantity);
                $('.cart_item.rpress_total span.cart-total').html(response.total);

                if ( response.cart_quantity == 0 ) {

                  $('.cart_item.rpress_subtotal,.rpress-cart-number-of-items,.cart_item.rpress_checkout,.cart_item.rpress_cart_tax,.cart_item.rpress_total').hide();
                  $('.rpress-cart').each(function() {

                    var cart_wrapper = $(this).parent();

                    if ( cart_wrapper ) {
                      cart_wrapper.addClass('cart-empty')
                      cart_wrapper.removeClass('cart-not-empty');
                    }

                    $(this).append('<li class="cart_item empty">' + rpress_scripts.empty_cart_message + '</li>');
                  });
                }

                $(document.body).trigger('rpress_cart_item_removed', [response]);

                $('.rpress-cart >li.rpress-cart-item').each( function( index, item ) {
                  $(this).find("[data-cart-item]").attr('data-cart-item', index);
                });

                // check if no item in cart left
                if ($('li.rpress-cart-item').length == 0) {
                  $('a.rpress-clear-cart').trigger('click');
                  $('li.delivery-items-options').hide();
                  $('a.rpress-clear-cart').hide();
                }

              }
            }
        });

        return false;
    });
});


// Load a payment gateway
function rpress_load_gateway(payment_mode) {

    // Show the ajax loader
    jQuery('.rpress-cart-ajax').show();
    jQuery('#rpress_purchase_form_wrap').html('<span class="rpress-loading-ajax rpress-loading"></span>');

    var url = rpress_scripts.ajaxurl;

    if (url.indexOf('?') > 0) {
        url = url + '&';
    } else {
        url = url + '?';
    }

    url = url + 'payment-mode=' + payment_mode;

    jQuery.post(url, {
            action: 'rpress_load_gateway',
            rpress_payment_mode: payment_mode
        },
        function(response) {
            jQuery('#rpress_purchase_form_wrap').html(response);
            jQuery('.rpress-no-js').hide();
            jQuery('body').trigger('rpress_gateway_loaded', [payment_mode]);
        }
    );
}
