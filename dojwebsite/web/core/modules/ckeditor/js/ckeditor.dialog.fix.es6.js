/**
 * @file
 * This file overrides the way jQuery UI focus trap works.
 *
 * When a focus event is fired while a CKEditor 4 instance is focused, do not
 * trap the focus and let CKEditor 4 manage that focus.
 */

(($) => {
  $.widget('ui.dialog', $.ui.dialog, {
    // Override core override of jQuery UI's `_allowInteraction()` so that
    // CKEditor 4 in modals can work as expected.
    // @see https://api.jqueryui.com/dialog/#method-_allowInteraction
    _allowInteraction(event) {
      return (
        $(event.target).closest('.cke_dialog').length || this._super(event)
      );
    },
  });
})(jQuery);
