<?php

namespace Drupal\ckeditor_a11ychecker\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;
use Drupal\Core\DrupalKernel;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the "a11ychecker" plugin.
 *
 * @CKEditorPlugin(
 *   id = "a11ychecker",
 *   label = @Translation("Accessibility Checker")
 * )
 */
class A11yCheckerPlugin extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return ['balloonpanel'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return ['core/drupal.jquery'];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'A11ychecker' => [
        'label' => $this->t('Accessibility Checker'),
        'image' => $this->librariesGetPath('a11ychecker') . '/icons/a11ychecker.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    if ($library_path = $this->librariesGetPath('a11ychecker')) {
      $library_path = $library_path . '/plugin.js';
    }
    return $library_path ?? "";
  }

  /**
   * Gets the path of a library.
   *
   * @param string $name
   *   The machine name of a library to return the path for.
   * @param string $base_path
   *   Whether to prefix the resulting path with base_path().
   *
   * @return string
   *   The path to the specified library or FALSE if the library wasn't found.
   */
  private function librariesGetPath($name, $base_path = NULL) {
    $libraries = &drupal_static(__FUNCTION__);
    if (!isset($libraries)) {
      $libraries = $this->getLibraryPaths();
    }
    $path = ($base_path ? base_path() : '');
    if (!isset($libraries[$name])) {
      return "";
    }
    else {
      $path .= $libraries[$name];
    }
    return $path;
  }

  /**
   * Returns an array of library directories.
   *
   * @return array
   *   A list of library directories.
   */
  private function getLibraryPaths() {
    $searchdir = [];
    $config = DrupalKernel::findSitePath(\Drupal::request());
    // Similar to 'modules' and 'themes' directories inside an installation
    // profile, installation profiles may want to place libraries into a
    // 'libraries' directory.
    if ($profile = \Drupal::installProfile()) {
      $profile_path = drupal_get_path('profile', $profile);
      $searchdir[] = "$profile_path/libraries";
      $searchdir[] = "$profile_path/libraries/ckeditor/plugins";
    };
    // Search sites/all/libraries for backwards-compatibility.
    $searchdir[] = 'sites/all/libraries';
    $searchdir[] = 'sites/all/libraries/ckeditor/plugins';
    // Always search the root 'libraries' directory.
    $searchdir[] = 'libraries';
    $searchdir[] = 'libraries/ckeditor/plugins';
    // Also search sites/<domain>/*.
    $searchdir[] = "$config/libraries";
    $searchdir[] = "$config/libraries/ckeditor/plugins";
    // Retrieve list of directories.
    $directories = [];
    $nomask = ['CVS'];
    foreach ($searchdir as $dir) {
      if (is_dir($dir) && $handle = opendir($dir)) {
        while (FALSE !== ($file = readdir($handle))) {
          if (!in_array($file, $nomask) && $file[0] != '.') {
            if (is_dir("$dir/$file")) {
              $directories[$file] = "$dir/$file";
            }
          }
        }
        closedir($handle);
      }
    }
    return $directories;
  }

}
