if(typeof BBF == "undefined") {
  throw new Error("BBF is not defined.");
}
// Extend the BBF object
jQuery.extend(BBF, {
  /**
   * Is an AJAX request runnning?
   * @type {bool}
   */
  doing_ajax: false,
  /**
   * Array of ajax requests
   * @type {array}
   */
  ajax_queue: [],
  /**
   * Add a ajax response to the queue
   * @type {function}
   * @param {string} action
   * @param {object} data
   * @param {function} callback
   * @return void
   */
  queue_ajax: function(action, data, callback) {
    // Make data optional
    if(typeof data === "function") {
      callback = data;
      data = {};
    }
    BBF.ajax_queue.push([action, data, callback]);
  },
  /**
   * Post the ajax queue
   * @type {function}
   * @return {void}
   */
  post_ajax_queue: function() {
    BBF.create_loader();
    if(!BBF.ajax_queue.length) {
      BBF.destroy_loader();
      return;
    }
    let percentage = 100 / BBF.ajax_queue.length;
    jQuery('body').append('<style>.bbfootball-ajax-loader:after { width: ' + percentage + '%; }</style>')

    // Change the flag
    BBF.doing_ajax = true;
    // Get the first entry
    var args = BBF.ajax_queue.shift();
    BBF.ajax_request(args[0], args[1], function(data) {
      if(args[2] && typeof args[2] == "function") {
        args[2](data);
      }

      // Are we done?
      if(BBF.ajax_queue.length) {
        BBF.post_ajax_queue();
      }
      else {
        BBF.destroy_loader();
        BBF.doing_ajax = false;
      }
    })
  },
  /**
   * Peform an ajax request
    * @type {function}
    * @param {string} action
    * @param {object} data
    * @param {function} callback
    * @return {jqXHR}
    */
  ajax_request: function(action, data, callback) {
    // Make data optional
    if(typeof data === "function") {
      callback = data;
      data = {};
    }

    jQuery.ajax({
      url: BBF.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: action,
        data: data
      },
      success: callback,
      error: function(jqXHR, status) {
        if(callback) {
          callback(null, status, jqXHR);
        }
      }
    });
    // return jqXHR;
  },

  /**
   * Create the loader if it doesn't exist
   */
  create_loader: function() {
    var $loader = jQuery('.bbfootball-ajax-loader');
    if($loader.length) {
      return;
    }

    $loader = jQuery('<div class="bbfootball-ajax-loader" />');
    jQuery('body').append($loader);
  },

  /**
   * Destroy the loader
   */
  destroy_loader: function() {
    var $loader = jQuery('.bbfootball-ajax-loader');
    $loader.remove();
  }
});

jQuery(function() {
  jQuery(document).foundation();

  // Init any found AJAX elements
  var ajax_elelements = jQuery('.bbfootball-ajax');
  if(ajax_elelements.length) {
    jQuery.each(ajax_elelements, function(index, element) {
      run_ajax_shortcode(element);
    });
    BBF.post_ajax_queue();
  }

  function run_ajax_shortcode(element) {
    var $element = jQuery(element);
    var attributes = [];
    data = {
      country: $element.attr('country'),
      league: $element.attr('league'),
      event: $element.attr('event'),
      show_title: $element.attr('show_title'),
      event_summary: $element.attr('event_summary'),
      last_update: $element.attr('last_update')
    }
    BBF.queue_ajax('bbfootball_ajax_elemet', data, function(response) {
      if(response.success) {
        // // If the response contains a shortcode - add it to the queue
        // var $response = jQuery(response.data);
        // var nested = $response.find('.bbfootball-ajax');
        // if(nested.length) {
        //   jQuery.each(nested, function(nested_index, nested_element) {
        //     var $nested_element = jQuery(nested_element);
        //     nested_data = {
        //       country: $nested_element.attr('country'),
        //       league: $nested_element.attr('league'),
        //       event: $nested_element.attr('event'),
        //       show_title: $nested_element.attr('show_title'),
        //       event_summary: $nested_element.attr('event_summary')
        //     }
        //     BBF.ajax_request('bbfootball_ajax_elemet', nested_data, function(nested_response) {
        //       if(response.success) {
        //         $nested_element.html(response.data);
        //       }
        //     })
        //
        //   });
        // }


        // Is the response telling us something?
        if(response.data == "__remove__") {
          $element.remove();
        }
        else {
          $element.html(response.data).foundation();
        }

      }
    });
  }

  // Setup the markets dropdown
  var markets_dropdown = jQuery('ul[data-event-markets]');
  markets_dropdown.find('li > a').on('click', function(e) {
    e.preventDefault();
    var event = jQuery(this).data('event'),
        market = jQuery(this).data('market');

    BBF.ajax_request('bbfootball_get_market_odds', {event:event, market:market}, function(response) {
      if(response.success) {
        jQuery('[data-event-odds]').html(response.data);
      }
    });
  });

  // Change the odds
  var switcher = jQuery();
  jQuery(document).on('change', '.bbfootball-odds-switcher > .switch .switch-input', function() {
    // let checked = jQuery(this).is(':checked');
    jQuery('.bookie-odds > span').toggleClass('hide');
  });

  // Post the queue
  BBF.post_ajax_queue();
  // console.log(BBF.ajax_queue);
})
