<?php

namespace Drupal\ckeditor_test\Plugin\CKEditorPlugin;

/**
 * Defines a broken plugin, to test that we fail gracefully when this happens.
 *
 * @CKEditorPlugin(
 *   id = "llama_broken",
 *   label = @Translation("Llama - Broken")
 * )
 */
class LlamaBroken extends LlamaContextual {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    // This simulates a contrib or custom plugin that is unable to figure out
    // its library file and returns empty. We shouldn't break hard when this
    // happens.
    return '';
  }

}
