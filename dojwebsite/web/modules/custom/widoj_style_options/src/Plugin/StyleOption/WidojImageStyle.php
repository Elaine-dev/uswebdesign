<?php

declare(strict_types=1);

namespace Drupal\widoj_style_options\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "widoj_image_style",
 *   label = @Translation("WIDOJ image Style")
 * )
 */
class WidojImageStyle extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $form['image_style'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('image_style') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [
        'style' => [$this->getConfiguration()['image_style'] ?? ''],
      ],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('options')) {
      $form['image_style']['#type'] = 'select';
      $options = $this->getConfiguration()['options'];
      if (
        class_exists('\Drupal\image_radios\Element\ImageRadios') &&
        count(array_filter($options, function ($option) {
          return isset($option['image']);
        }))) {

        $form['image_style']['#type'] = 'image_radios';
      }
      else {
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $form['image_style']['#multiple'] = TRUE;
        }
      }

      $form['image_style']['#options'] = $options;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('image_style') ?? NULL;
    $option_definition = $this->getConfiguration()['options'];

    if (is_array($value)) {
      $style = implode(' ',
        array_map(function ($index) use ($option_definition) {
          return $option_definition[$index]['class'] ?? NULL;
        }, $value)
      );
    }
    else {
      $style = $this->getConfiguration()['options'][$value]['class'] ?? NULL;
    }
    if (!empty($style)) {
      $build['#attributes']['image_style'] = $style;
    }
    return $build;
  }

}
