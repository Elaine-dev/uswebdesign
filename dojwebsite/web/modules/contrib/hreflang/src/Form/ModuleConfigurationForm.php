<?php

namespace Drupal\hreflang\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ModuleConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hreflang_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'hreflang.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hreflang.settings');
    $form['x_default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add x-default hreflang tag for default language'),
      '#default_value' => $config->get('x_default'),
      '#description' => $this->t('If enabled, an additional <a href="https://en.wikipedia.org/wiki/Hreflang#X-Default" rel="noreferrer">@html</a> tag will be created, pointing at the site default language.', ['@html' => 'hreflang="x-default"']),
    ];

    $form['defer_to_content_translation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Defer to Content Translation hreflang tags on content entity pages'),
      '#default_value' => $config->get('defer_to_content_translation'),
      '#description' => $this->t("If enabled, and Content Translation module is enabled, Hreflang module will not add hreflang tags to content entity pages (aside from the x-default tag, if enabled above). As a result, hreflang tags will be added only for languages that have a translation (and to which the user has view access), or for the content's designated language if it is not translatable, although the content could still be accessible under other languages with a translated user interface. Note that Content Translation module does not add query arguments to its hreflang tags, so pages with query arguments will not have a valid set of hreflang tags; this will, however, improve cache efficiency by not creating separate caches for each set of query arguments."),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('hreflang.settings')
      ->set('x_default', $form_state->getValue('x_default'))
      ->set('defer_to_content_translation', $form_state->getValue('defer_to_content_translation'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
