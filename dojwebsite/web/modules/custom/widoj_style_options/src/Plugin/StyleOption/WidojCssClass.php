<?php

declare(strict_types=1);

namespace Drupal\widoj_style_options\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "widoj_css_class",
 *   label = @Translation("WIDOJ CSS Class")
 * )
 */
class WidojCssClass extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $form['css_class'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('css_class') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [
        'class' => [$this->getConfiguration()['css_class'] ?? ''],
      ],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('options')) {
      $form['css_class']['#type'] = 'select';
      $options = $this->getConfiguration()['options'];
      if (
        class_exists('\Drupal\image_radios\Element\ImageRadios') &&
        count(array_filter($options, function ($option) {
          return isset($option['image']);
        }))) {

        $form['css_class']['#type'] = 'image_radios';
      }
      else {
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $form['css_class']['#multiple'] = TRUE;
        }
      }

      $form['css_class']['#options'] = $options;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('css_class') ?? NULL;
    $option_definition = $this->getConfiguration()['options'];
    if (is_array($value)) {
      $class = implode(' ',
        array_map(function ($index) use ($option_definition) {
          return $option_definition[$index]['class'] ?? NULL;
        }, $value)
      );
    }
    else {
      $class = $this->getConfiguration()['options'][$value]['class'] ?? NULL;
    }
    if (!empty($class)) {
      $build['#attributes']['class'][] = $class;
    }
    return $build;
  }

}
