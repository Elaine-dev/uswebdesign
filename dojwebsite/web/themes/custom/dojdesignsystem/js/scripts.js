(function ($, Drupal) {
    Drupal.behaviors.tabAccordion = {
        attach: function (context, settings) {
            if ($(context).find('.paragraph--type--tabs-accordion').length > 0) {
                $(context).find('.paragraph--type--tabs-accordion').each(function () {

                    // show default tab content on page load
                    var defaultTab = $(this).find('.current-tab');
                    var tabId = defaultTab.attr('id').split("_");
                    var tabContent = $(this).find('#tabcontent_' + tabId[1]);

                    tabContent.addClass('tab-content-visible');
                });
                $(document).ajaxComplete(function (e, xhr, settings) {
                    // show default tab content on page load
                    var defaultTab = $(this).find('.paragraph--type--tabs-accordion .tab-button:first-of-type');
                    defaultTab.addClass('current-tab');
                    var currentTab = $(this).find('.paragraph--type--tabs-accordion .current-tab');
                    var tabId = currentTab.attr('id').split("_");
                    var tabContent = $(this).find('#tabcontent_' + tabId[1]);

                    tabContent.addClass('tab-content-visible');
                });

                // hide/show tab content based on tab selection
                $(context).find(".paragraph--type--tabs-accordion" +
                    " .tab-button").click(function (e) {
                    var activeTab = $(this);
                    var tabId = activeTab.attr('id').split("_");
                    var tabContent = $(context).find('#tabcontent_' + tabId[1]);
                    var inactiveTabs = $(this).siblings(".tab-button");
                    // Set active tab
                    $(this).siblings(".paragraph--type--tabs-accordion" +
                        " .tab-button").removeClass("current-tab");
                    $(this).addClass("current-tab");

                    // Hide inactive tab content
                    inactiveTabs.each(function () {
                        var inactiveTabId = $(this).attr('id').split("_");
                        var intactiveTabContent = $(context).find('.paragraph--id--' + inactiveTabId[1]);
                        intactiveTabContent.find('.tab-content').removeClass("tab-content-visible");

                    });
                    // toggle active class on tab
                    // add hide/show content based on active tab
                    tabContent.addClass("tab-content-visible");
                });
            }
        }
    } //Accordion Tabs


    Drupal.behaviors.dtagovauChosenAccessibilityFix = {
        attach: function(context, settings) {
          jQuery('body').on('chosen:ready', function(evt, params) {

            jQuery('.js-form-item.js-form-type-select', context).each(function(index, element) {

              jQuery(this).find('.chosen-container-multi input.chosen-search-input').attr('aria-label', jQuery.trim(jQuery(this).find('label').text()));
              console.log(jQuery(this).find('.chosen-container-multi input.chosen-search-input'));

            });
          });
        }
      }

})(jQuery, Drupal);