(function($) {
  $(document).on('ite-pre-ajax-complete', function (e, data) {
    if (!data.hasOwnProperty('blocks')) {
      return;
    }

    var blocks = data['blocks'];
    $.each(blocks, function(selector, blockData) {
      var $element = $(selector);

      var event = $.Event('ite-before-show.block');
      $element.trigger(event, [blockData]);
      if (false === event.result) {
        return;
      }

      var $content = $(blockData.content);
      $content.hide();

      $element
        .html($content)
        .trigger('ite-show.block', blockData)
      ;

      var afterShow = function() {
        $element.trigger('ite-shown.block', blockData);
      };

      var showAnimationLength = blockData['show_animation']['length'];
      switch (blockData['show_animation']['type'].toLowerCase()) {
        case 'fade':
          $content.fadeIn(showAnimationLength, afterShow);
          break;
        case 'slide':
          $content.slideDown(showAnimationLength, afterShow);
          break;
        case 'show':
          $content.show(showAnimationLength, afterShow);
          break;
        default:
          $content.show(null, afterShow);
          break;
      }

    });
  });
})(jQuery);