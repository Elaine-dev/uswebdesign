(function ($, Drupal) {
    Drupal.behaviors.widojStyleOptionsTabs = {
        // Add tab style to the layout paragraphs modal
        attach: function (context) {
                // Run after ajax has processed
                $(document).ajaxComplete(function (e, xhr, settings) {

                    // Focus on ckeditor dialog instead of the
                    // layout paragraphs dialog
                    jQuery.ui.dialog.prototype._focusTabbable = function () { };

                    if($(context).find('.layout-paragraphs-tabs').length > 0) {

                        // Set active tab class
                        if($(context).find('.layout_paragraphs_modal_layout_tab').length > 0) {
                            $(context).find('.layout_paragraphs_modal_layout_tab').addClass('current-tab');
                        } else {
                            $(context).find('.layout_paragraphs_modal_fields_tab').addClass('current-tab');
                        }

                        // Create variables for each of the tab contents
                        var layout = $(context).find('#layout-paragraphs-element');
                        var styles = $(context).find('#layout-paragraphs-styles');
                        // var content = $(context).find('#layout-paragraphs-fields');
                        var content =
                        $('.layout-paragraphs-component-form')
                            .children()
                            .not('#layout-paragraphs-styles')
                            .not('#layout-paragraphs-element');

                        // Set the default tab's content as visible
                        if($(context).find(layout).length > 0) {
                            $(context).find(layout).addClass('tab-content-visible');
                        } else {
                            $(context).find(content).addClass('tab-content-visible');
                        }

                        $(".layout-paragraphs-tabs li > a").click(function(e) {
                            var activeTab = $(this).parent();

                            $(".current-tab").removeClass("current-tab");
                            $(".tab-content-visible").removeClass("tab-content-visible");
                            $(this).closest("li").addClass("current-tab");

                            if(activeTab.hasClass('layout_paragraphs_modal_layout_tab')) {
                                $(layout).addClass("tab-content-visible");
                            }
                            if(activeTab.hasClass('layout_paragraphs_modal_styles_tab')) {
                                styles.addClass("tab-content-visible");
                            }
                            if(activeTab.hasClass('layout_paragraphs_modal_fields_tab')) {
                                content.addClass("tab-content-visible");
                            }

                            e.preventDefault();
                        });
                    };
                });
        }
    };

    // General JS fixes for the Styles Options go here
    Drupal.behaviors.widojStyleOptionsFixes = {
        attach: function (context) {
            // Fix the accordion default state in layout popup
            $(document).ajaxComplete(function (e, xhr, settings) {
                if ($(context).find('.usa-accordion').length > 0) {
                    $(context).find('.usa-accordion').once('findAccordions').each(function () {
                        $(this).find('li').each(function () {
                            var button = $(this).find('button');
                            var isExpanded = button[0].ariaExpanded;
                            if (isExpanded == 'false') button.next().prop("hidden", true);
                        });
                    });
                }
            });
        }
    }
})(jQuery, Drupal);