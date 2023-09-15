<?php

namespace Drupal\Tests\hreflang\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for presence of the hreflang link element.
 *
 * @group hreflang
 */
class HreflangDynamicPageCacheTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['hreflang', 'language', 'dynamic_page_cache'];

  /**
   * Functional tests for the hreflang tag.
   */
  public function testHreflangTag() {
    global $base_url;
    // User to add language.
    $admin_user = $this->drupalCreateUser([
      'administer languages',
      'access administration pages',
    ]);
    $this->drupalLogin($admin_user);
    // Add predefined language.
    $this->drupalGet('admin/config/regional/language/add');
    $edit = ['predefined_langcode' => 'fr'];
    $this->submitForm($edit, 'Add language');

    // Setup a new user with cached hreflang tags.
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($user);
    $this->drupalGet('user/3');
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/user/3?check_logged_in=1" />');
    $this->assertSession()->responseNotContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/user/3?check_logged_in=1" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="fr" href="' . $base_url . '/fr/user/3" />');
    $this->assertSession()->responseContains('<link rel="alternate" hreflang="en" href="' . $base_url . '/user/3" />');
  }

}
