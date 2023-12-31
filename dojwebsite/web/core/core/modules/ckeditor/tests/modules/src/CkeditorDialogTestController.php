<?php

namespace Drupal\ckeditor_test;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;

/**
 * Provides controller for testing CKEditor in jQuery UI dialogs.
 */
class CkeditorDialogTestController {

  /**
   * Returns a link that can open a node add form in a modal dialog.
   *
   * @return array
   *   A render array.
   */
  public function testDialog() {
    $build['link'] = [
      '#type' => 'link',
      '#title' => 'Add Node',
      '#url' => Url::fromRoute('node.add', ['node_type' => 'page']),
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => 700,
        ]),
      ],
    ];
    $build['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $build;
  }

}
