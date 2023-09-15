(($, Drupal) => {
  function initColorSpectrum(context, settings) {
    $('[data-color-spectrum-id]')
      .once('option-plugin-color-spectrum')
      .each((i, e) => {
        const $e = $(e);
        const id = $(e).attr('data-color-spectrum-id');
        const options = $.extend({}, settings[id] || {}, {
          preferredFormat: 'rgb',
        });
        if (options.palette) {
          options.showPalette = true;
        }
        $e.spectrum(options);
      });
  }
  Drupal.behaviors.optionPluginColorSpectrum = {
    attach: function attach(context, settings) {
      setTimeout(() => {
        initColorSpectrum(context, settings);
      }, 500);
    },
  };
})(jQuery, Drupal);
