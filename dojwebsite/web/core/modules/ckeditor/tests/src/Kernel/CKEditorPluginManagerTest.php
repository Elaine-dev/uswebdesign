<?php

namespace Drupal\Tests\ckeditor\Kernel;

use Drupal\editor\Entity\Editor;
use Drupal\KernelTests\KernelTestBase;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests different ways of enabling CKEditor plugins.
 *
 * @group ckeditor
 */
class CKEditorPluginManagerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'user',
    'filter',
    'editor',
    'ckeditor',
  ];

  /**
   * The manager for "CKEditor plugin" plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  protected function setUp(): void {
    parent::setUp();

    // Install the Filter module.

    // Create text format, associate CKEditor.
    $filtered_html_format = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
      'filters' => [],
    ]);
    $filtered_html_format->save();
    $editor = Editor::create([
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ]);
    $editor->save();
  }

  /**
   * Tests the enabling of plugins.
   */
  public function testEnabledPlugins() {
    $this->manager = $this->container->get('plugin.manager.ckeditor.plugin');
    $editor = Editor::load('filtered_html');

    // Case 1: no CKEditor plugins.
    $definitions = array_keys($this->manager->getDefinitions());
    sort($definitions);
    $this->assertSame(['drupalimage', 'drupalimagecaption', 'drupallink', 'internal', 'language', 'stylescombo'], $definitions, 'No CKEditor plugins found besides the built-in ones.');
    $enabled_plugins = [
      'drupalimage' => $this->getModulePath('ckeditor') . '/js/plugins/drupalimage/plugin.js',
      'drupallink' => $this->getModulePath('ckeditor') . '/js/plugins/drupallink/plugin.js',
    ];
    $this->assertSame($enabled_plugins, $this->manager->getEnabledPluginFiles($editor), 'Only built-in plugins are enabled.');
    $this->assertSame(['internal' => NULL] + $enabled_plugins, $this->manager->getEnabledPluginFiles($editor, TRUE), 'Only the "internal" plugin is enabled.');

    // Enable the CKEditor Test module, which has the Llama plugin (plus four
    // variations of it, to cover all possible ways a plugin can be enabled) and
    // clear the editor manager's cache so it is picked up.
    $this->enableModules(['ckeditor_test']);
    $this->manager = $this->container->get('plugin.manager.ckeditor.plugin');
    $this->manager->clearCachedDefinitions();

    // Case 2: CKEditor plugins are available.
    $plugin_ids = array_keys($this->manager->getDefinitions());
    sort($plugin_ids);
    $this->assertSame(['drupalimage', 'drupalimagecaption', 'drupallink', 'internal', 'language', 'llama', 'llama_broken', 'llama_button', 'llama_contextual', 'llama_contextual_and_button', 'llama_css', 'stylescombo'], $plugin_ids, 'Additional CKEditor plugins found.');
    $this->assertSame($enabled_plugins, $this->manager->getEnabledPluginFiles($editor), 'Only the internal plugins are enabled.');
    $this->assertSame(['internal' => NULL] + $enabled_plugins, $this->manager->getEnabledPluginFiles($editor, TRUE), 'Only the "internal" plugin is enabled.');

    // Case 3: enable each of the newly available plugins, if possible:
    // 1. Llama: cannot be enabled, since it does not implement
    //    CKEditorPluginContextualInterface nor CKEditorPluginButtonsInterface.
    // 2. LlamaContextual: enabled by adding the 'Strike' button, which is
    //    part of another plugin!
    // 3. LlamaButton: automatically enabled by adding its 'Llama' button.
    // 4. LlamaContextualAndButton: enabled by either 2 or 3.
    // 5. LlamaCSS: automatically enabled by add its 'LlamaCSS' button.
    // Below, we will first enable the "Llama" button, which will cause the
    // LlamaButton and LlamaContextualAndButton plugins to be enabled. Then we
    // will remove the "Llama" button and add the "Strike" button, which will
    // cause the LlamaContextual and LlamaContextualAndButton plugins to be
    // enabled. Then we will add the "Strike" button back again, which would
    // cause LlamaButton, LlamaContextual and LlamaContextualAndButton to be
    // enabled. Finally, we will add the "LlamaCSS" button which would cause
    // all four plugins to be enabled.
    $settings = $editor->getSettings();
    $original_toolbar = $settings['toolbar'];
    $settings['toolbar']['rows'][0][0]['items'][] = 'Llama';
    $editor->setSettings($settings);
    $editor->save();
    $file = [];
    $file['b'] = $this->getModulePath('ckeditor_test') . '/js/llama_button.js';
    $file['c'] = $this->getModulePath('ckeditor_test') . '/js/llama_contextual.js';
    $file['cb'] = $this->getModulePath('ckeditor_test') . '/js/llama_contextual_and_button.js';
    $file['css'] = $this->getModulePath('ckeditor_test') . '/js/llama_css.js';
    $expected = $enabled_plugins + ['llama_button' => $file['b'], 'llama_contextual_and_button' => $file['cb']];
    $this->assertSame($expected, $this->manager->getEnabledPluginFiles($editor), 'The LlamaButton and LlamaContextualAndButton plugins are enabled.');
    $this->assertSame(['internal' => NULL] + $expected, $this->manager->getEnabledPluginFiles($editor, TRUE), 'The LlamaButton and LlamaContextualAndButton plugins are enabled.');
    $settings['toolbar'] = $original_toolbar;
    $settings['toolbar']['rows'][0][0]['items'][] = 'Strike';
    $editor->setSettings($settings);
    $editor->save();
    // Our test module provides a broken plugin that returns an empty string.
    $enabled_plugins = ['llama_broken' => ''] + $enabled_plugins;
    $expected = $enabled_plugins + ['llama_contextual' => $file['c'], 'llama_contextual_and_button' => $file['cb']];
    $this->assertSame($expected, $this->manager->getEnabledPluginFiles($editor), 'The  LLamaContextual and LlamaContextualAndButton plugins are enabled.');
    $this->assertSame(['internal' => NULL] + $expected, $this->manager->getEnabledPluginFiles($editor, TRUE), 'The LlamaContextual and LlamaContextualAndButton plugins are enabled.');
    $settings['toolbar']['rows'][0][0]['items'][] = 'Llama';
    $editor->setSettings($settings);
    $editor->save();
    $expected = $enabled_plugins + ['llama_button' => $file['b'], 'llama_contextual' => $file['c'], 'llama_contextual_and_button' => $file['cb']];
    $this->assertSame($expected, $this->manager->getEnabledPluginFiles($editor), 'The LlamaButton, LlamaContextual and LlamaContextualAndButton plugins are enabled.');
    $this->assertSame(['internal' => NULL] + $expected, $this->manager->getEnabledPluginFiles($editor, TRUE), 'The LLamaButton, LlamaContextual and LlamaContextualAndButton plugins are enabled.');
    $settings['toolbar']['rows'][0][0]['items'][] = 'LlamaCSS';
    $editor->setSettings($settings);
    $editor->save();
    $expected = $enabled_plugins + ['llama_button' => $file['b'], 'llama_contextual' => $file['c'], 'llama_contextual_and_button' => $file['cb'], 'llama_css' => $file['css']];
    $this->assertSame($expected, $this->manager->getEnabledPluginFiles($editor), 'The LlamaButton, LlamaContextual, LlamaContextualAndButton and LlamaCSS plugins are enabled.');
    $this->assertSame(['internal' => NULL] + $expected, $this->manager->getEnabledPluginFiles($editor, TRUE), 'The LLamaButton, LlamaContextual, LlamaContextualAndButton and LlamaCSS plugins are enabled.');
  }

  /**
   * Tests the iframe instance CSS files of plugins.
   */
  public function testCssFiles() {
    $this->manager = $this->container->get('plugin.manager.ckeditor.plugin');
    $editor = Editor::load('filtered_html');

    // Case 1: no CKEditor iframe instance CSS file.
    $this->assertSame([], $this->manager->getCssFiles($editor), 'No iframe instance CSS file found.');

    // Enable the CKEditor Test module, which has the LlamaCss plugin and
    // clear the editor manager's cache so it is picked up.
    $this->enableModules(['ckeditor_test']);
    $this->manager = $this->container->get('plugin.manager.ckeditor.plugin');
    $settings = $editor->getSettings();
    // LlamaCss: automatically enabled by adding its 'LlamaCSS' button.
    $settings['toolbar']['rows'][0][0]['items'][] = 'LlamaCSS';
    $editor->setSettings($settings);
    $editor->save();

    // Case 2: CKEditor iframe instance CSS file.
    $expected = [
      'llama_css' => [$this->getModulePath('ckeditor_test') . '/css/llama.css'],
    ];
    $this->assertSame($expected, $this->manager->getCssFiles($editor), 'Iframe instance CSS file found.');
  }

}
