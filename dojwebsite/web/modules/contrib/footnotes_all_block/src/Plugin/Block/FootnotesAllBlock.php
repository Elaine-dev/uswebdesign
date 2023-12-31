<?php

namespace Drupal\footnotes_all_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Block that displays every footnote in the page.
 *
 * @package Drupal\footnotes_all_block\Plugin\Block
 * @Block(
 *   id = "footnotes_all_block",
 *   admin_label = @Translation("Block containing every footnote"),
 * )
 */
class FootnotesAllBlock extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build(): array {
    return [
      'list' => [
        // We can't use item_list because Drupal will not render it if empty.
        '#type' => 'html_tag',
        '#attributes' => [
          'class' => ['footnotes'],
          'id' => 'footnotes_all_block',
        ],
        '#tag' => 'ul',
      ],
      '#attached' => [
        'library' => ['footnotes_all_block/footnotes_all_block'],
      ],
    ];
  }

}
