(function ($) {
  'use strict';

  // Only provide a fallback when jQuery UI Sortable is not available.
  if (!$.fn || typeof $.fn.sortable === 'function') {
    return;
  }

  $.fn.sortable = function (options) {
    var settings = $.extend({
      items: 'tr',
      tolerance: 'pointer',
      start: null,
      sort: null
    }, options || {});

    return this.each(function () {
      var $container = $(this);
      var dragSource = null;
      var placeholder = null;

      function getRows() {
        return $container.children(settings.items);
      }

      function createPlaceholder($row) {
        var colspan = $row.children('td,th').length || 1;
        var height = $row.outerHeight();
        var $placeholder = $('<tr class="ui-sortable-placeholder" />');
        var $cell = $('<td />', { colspan: colspan }).css('height', height);
        $placeholder.append($cell);
        return $placeholder;
      }

      function getUi() {
        return {
          helper: dragSource,
          placeholder: placeholder
        };
      }

      function activateRows() {
        getRows().each(function () {
          this.draggable = true;
        });
      }

      function clearState() {
        if (dragSource && dragSource.length) {
          dragSource.removeClass('ui-sortable-helper').css('display', '');
        }
        if (placeholder && placeholder.length) {
          placeholder.remove();
        }
        dragSource = null;
        placeholder = null;
      }

      $container.addClass('ui-sortable');
      activateRows();

      $container.on('dragstart', settings.items, function (event) {
        var originalEvent = event.originalEvent;
        dragSource = $(this);
        placeholder = createPlaceholder(dragSource);

        dragSource.addClass('ui-sortable-helper');
        dragSource.after(placeholder);

        if (originalEvent && originalEvent.dataTransfer) {
          originalEvent.dataTransfer.effectAllowed = 'move';
          originalEvent.dataTransfer.setData('text/plain', 'sortable');
        }

        setTimeout(function () {
          if (dragSource && dragSource.length) {
            dragSource.css('display', 'none');
          }
        }, 0);

        if (typeof settings.start === 'function') {
          settings.start.call($container[0], event, getUi());
        }
      });

      $container.on('dragover', function (event) {
        if (!dragSource || !dragSource.length || !placeholder || !placeholder.length) {
          return;
        }

        event.preventDefault();

        var $target = $(event.target).closest(settings.items, $container);
        if (!$target.length || $target.is(dragSource) || $target.is(placeholder)) {
          return;
        }

        var targetTop = $target.offset().top;
        var targetMiddle = targetTop + ($target.outerHeight() / 2);

        if (event.originalEvent.pageY < targetMiddle) {
          $target.before(placeholder);
        } else {
          $target.after(placeholder);
        }

        if (typeof settings.sort === 'function') {
          settings.sort.call($container[0], event, getUi());
        }
      });

      $container.on('drop', function (event) {
        if (!dragSource || !placeholder) {
          return;
        }

        event.preventDefault();
        placeholder.replaceWith(dragSource.css('display', ''));
        activateRows();
      });

      $container.on('dragend', settings.items, function () {
        clearState();
      });
    });
  };
})(jQuery);
