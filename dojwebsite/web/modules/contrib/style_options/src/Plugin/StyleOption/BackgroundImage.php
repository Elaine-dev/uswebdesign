<?php

declare(strict_types=1);

namespace Drupal\style_options\Plugin\StyleOption;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Bytes;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Environment;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\style_options\StyleOptionStyleTrait;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the image attribute option plugin.
 *
 * @StyleOption(
 *   id = "background_image",
 *   label = @Translation("Image Attribute")
 * )
 */
class BackgroundImage extends StyleOptionPluginBase {

  use AjaxHelperTrait;
  use StyleOptionStyleTrait;

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $scheme = $this->getConfiguration()['scheme'] ?? 'public';
    $directory = $this->getConfiguration()['directory'] ?? '';
    $env_max_upload_size = Environment::getUploadMaxSize();
    $max_size = $this->getConfiguration()['max_size'] ?? $env_max_upload_size;
    $max_filesize = min(Bytes::toInt($max_size), $env_max_upload_size);
    $max_dimensions = $this->getConfiguration()['max_dimmensions'] ?? 0;

    $form['fid'] = [
      '#title' => $this->getLabel(),
      '#type' => 'managed_file',
      '#upload_location' => $scheme . '://' . $directory,
      '#default_value' => $this->getValue('fid') ?? $this->getDefaultValue(),
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_size' => [$max_filesize],
        'file_validate_image_resolution' => [$max_dimensions],
      ],
      '#wrapper_attributes' => [
        'class' => [$this->getConfiguration()['css_class'] ?? ''],
      ],
      '#description' => $this->getConfiguration('description'),
    ];
    return $form;

  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $values = $form_state->cleanValues()->getValues();
    if (isset($values['fid'][0])) {
      $fid = $values['fid'][0];
      /** @var \Drupal\file\Entity\File $file */
      $file = $this->entityTypeManager->getStorage('file')->load($fid);
      $file->setPermanent();
      $file->save();
    }
    $this->setValues($values);
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build, $value = '') {
    $fid = $this->getValue('fid');
    if (!empty($fid) && $file_object = File::load($fid[0])) {

      $file_uri = $file_object->getFileUri();
      $file_url = Url::fromUri(file_create_url($file_uri))->toString();

      if ($this->getConfiguration('method') == 'css') {
        $this->generateStyle($build, [
          '#file_url' => $file_url,
          '#image' => $file_object,
        ]);
      }
      else {
        $build['#attributes']['style'][] = 'background-image: url(' . $file_url . ');';
      }
    }
    return $build;
  }

}
