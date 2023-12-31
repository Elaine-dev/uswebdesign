<?php
namespace Drupal\widoj_style_options\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Configure example settings for this site.
 */
class WIDOJStyleOptionsSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'widoj_style_options.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'widoj_style_options_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['default_image_styles'] = array(
      "#type" => "details" ,
      "#title" => 'Image Style Defaults',
      "#open" => TRUE,
    );
    $styles = ImageStyle::loadMultiple();
    $image_styles = [];
    foreach ($styles as $style) {
      $image_styles[$style->getName()] = $style->getName();
    }
    $form['default_image_styles']['default_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Image Style'),
      '#default_value' => $config->get('default_image_style'),
      '#options' => $image_styles,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('default_image_style', $form_state->getValue('default_image_style'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}