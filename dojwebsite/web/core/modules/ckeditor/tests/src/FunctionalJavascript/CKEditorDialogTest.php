<?php

namespace Drupal\Tests\ckeditor\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\editor\Entity\Editor;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\ckeditor\Traits\CKEditorTestTrait;

/**
 * Tests for CKEditor 4 to ensure correct focus management in dialogs.
 *
 * @group ckeditor5
 * @internal
 */
class CKEditorDialogTest extends WebDriverTestBase {

  use CKEditorTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'ckeditor', 'filter', 'ckeditor_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a text format and associate CKEditor.
    FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
    ])->save();

    $editor = Editor::create([
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ]);

    // Add the table button.
    $settings = $editor->getSettings();
    $settings['toolbar']['rows'][0][0]['items'][] = 'Table';
    $editor->setSettings($settings);
    $editor->save();

    // Create a node type for testing.
    NodeType::create(['type' => 'page', 'name' => 'page'])->save();

    $field_storage = FieldStorageConfig::loadByName('node', 'body');

    // Create a body field instance for the 'page' node type.
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'page',
      'label' => 'Body',
      'settings' => ['display_summary' => TRUE],
      'required' => TRUE,
    ])->save();

    // Assign widget settings for the 'default' form mode.
    EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent('body', ['type' => 'text_textarea_with_summary'])
      ->save();

    $this->drupalLogin($this->drupalCreateUser([
      'administer nodes',
      'create page content',
      'use text format filtered_html',
    ]));
  }

  /**
   * Tests if CKEditor 4 dialogs can be interacted within jQueryUI dialogs.
   */
  public function testCKEditorFocusInCkeditorDialogWithinJqueryDialog() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalGet('/ckeditor_test/dialog');

    // Open the dialog modal.
    $page->clickLink('Add Node');
    $assert_session->waitForElementVisible('css', '.ui-dialog');
    $assert_session->assertWaitOnAjaxRequest();

    // Click the table button.
    $assert_session->elementExists('css', '.cke_button__table');
    $this->click('.cke_button__table');

    // Fill the rows field.
    $page->fillField('Rows', 4);
  }

}
