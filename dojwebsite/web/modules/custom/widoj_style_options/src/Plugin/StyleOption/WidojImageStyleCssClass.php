<?php

declare(strict_types=1);

namespace Drupal\widoj_style_options\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "widoj_image_style_css_class",
 *   label = @Translation("WIDOJ Combined Image Style / CSS Class")
 * )
 */
class WidojImageStyleCssClass extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {
    $form['image_appearance'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('image_appearance') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [
        'class' => [$this->getConfiguration()['image_appearance'] ?? ''],
      ],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('options')) {
      $form['image_appearance']['#type'] = 'select';
      $options = $this->getConfiguration()['options'];
      if (
        class_exists('\Drupal\image_radios\Element\ImageRadios') &&
        count(array_filter($options, function ($option) {
          return isset($option['image']);
        }))) {

        $form['image_appearance']['#type'] = 'image_radios';
      }
      else {
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $form['image_appearance']['#multiple'] = TRUE;
        }
      }

      $form['image_appearance']['#options'] = $options;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('image_appearance') ?? NULL;
    $option_definition = $this->getConfiguration()['options'];
    if (is_array($value)) {
      $class = implode(' ',
        array_map(function ($index) use ($option_definition) {
          return $option_definition[$index]['class'] ?? NULL;
        }, $value)
      );
      $style = implode(' ',
        array_map(function ($index) use ($option_definition) {
          return $option_definition[$index]['style'] ?? NULL;
        }, $value)
      );
      $image_side = implode(' ',
        array_map(function ($index) use ($option_definition) {
          return $option_definition[$index]['image_side'] ?? NULL;
        }, $value)
      );
    }
  else {
      $class = $this->getConfiguration()['options'][$value]['class'] ?? NULL;
      $style = $this->getConfiguration()['options'][$value]['style'] ?? NULL;
      $image_side = $this->getConfiguration()['options'][$value]['image_side'] ?? NULL;
    }
    if (!empty($class)) {
      $build['#attributes']['class'][] = $class;
    }
    if (!empty($style)) {
      $build['#attributes']['image_style'] = $style;
    }
    if (!empty($image_side)) {
      $build['#attributes']['image_side'] = $image_side;
    }
    return $build;
  }

}
