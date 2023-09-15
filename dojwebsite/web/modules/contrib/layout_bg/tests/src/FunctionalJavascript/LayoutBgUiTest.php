<?php

namespace Drupal\Tests\layout_bg\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\layout_builder\FunctionalJavascript\LayoutBuilderSortTrait;

/**
 * Tests the Layout Builder UI with Layout Bg sections.
 *
 * This is very closely modeled on LayoutBuilderTest.php from layout_builder.
 * It's nice to have coverage here because the layout_bg template is unusual.
 *
 * @group layout_bg
 */
class LayoutBgUiTest extends WebDriverTestBase {

  use LayoutBuilderSortTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_bg_tests',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'administer node display',
    ], 'foobar'));
  }

  /**
   * Tests the Layout Builder UI.
   */
  public function testLayoutBuilderUi() {
    $layout_url = 'admin/structure/types/manage/layout_bg_test_node/display/default/layout';
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Go to manage layout.
    $this->drupalGet($layout_url);
    // Turn off preview.
    $page->uncheckField('layout-builder-content-preview');

    // Two-column: Check that blocks start out in the right place.
    $assert_session->elementTextNotContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Layout BG Teaser Image');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Layout BG Hero Image');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="first"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="second"]', 'Body');

    // One-column: Check that blocks start out in the right place.
    $assert_session->elementTextNotContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Layout BG Teaser Image');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Layout BG Hero Image');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="content"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="content"]', 'Body');

    // Two-column: Drag the title block to background.
    $this->sortableTo('.layout--twocol-sectionhtmltwig [data-layout-content-preview-placeholder-label="\"Title\" field"]', '.layout--twocol-sectionhtmltwig [data-region="first"]', '.layout--twocol-sectionhtmltwig [data-region="background"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Title');
    $assert_session->elementTextNotContains('css', '.layout--twocol-sectionhtmltwig [data-region="first"]', 'Title');

    // One-column: Drag the title block to background.
    $this->sortableTo('.layout--onecolhtmltwig [data-layout-content-preview-placeholder-label="\"Title\" field"]', '.layout--onecolhtmltwig [data-region="content"]', '.layout--onecolhtmltwig [data-region="background"]');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementTextNotContains('css', '.layout--onecolhtmltwig [data-region="content"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Title');

    // Save and make sure the config sticks.
    $page->pressButton('Save layout');
    $this->drupalGet($layout_url);

    // Two-column.
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Layout BG Teaser Image');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="background"]', 'Layout BG Hero Image');
    $assert_session->elementTextNotContains('css', '.layout--twocol-sectionhtmltwig [data-region="first"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--twocol-sectionhtmltwig [data-region="second"]', 'Body');

    // One-column.
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Layout BG Teaser Image');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="background"]', 'Layout BG Hero Image');
    $assert_session->elementTextNotContains('css', '.layout--onecolhtmltwig [data-region="content"]', 'Title');
    $assert_session->elementTextContains('css', '.layout--onecolhtmltwig [data-region="content"]', 'Body');
  }

}
