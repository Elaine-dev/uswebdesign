/* global Drupal, jQuery */

Drupal.behaviors.footnotesAllBlock = {
  /**
   * @param context
   */
  attach: function (context) {
    // Move every footnote to the block.
    jQuery('.footnotes .footnote', context).appendTo(
      jQuery('#footnotes_all_block', context)
    );

    // Remove every footnote list except the one in the block.
     jQuery('.footnotes', context).not('#footnotes_all_block').remove();
    }
};
