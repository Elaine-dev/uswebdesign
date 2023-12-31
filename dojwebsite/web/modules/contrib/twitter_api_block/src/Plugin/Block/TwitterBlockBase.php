<?php

namespace Drupal\twitter_api_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'TwitterBlock' block.
 */
class TwitterBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The Twitter service.
   *
   * @var \Drupal\twitter_api_block\TwitterManagerInterface
   */
  protected $twitter;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory');
    $instance->twitter = $container->get('twitter_api_block.twitter');
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'application' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config  = $this->getConfiguration();

    $form['application'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Application'),
      '#default_value' => $config['application'] ?? NULL,
      '#key_filters' => ['type' => 'twitter_api_app'],
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#collapsible' => TRUE,
      '#open' => TRUE,
      '#weight' => 1,
    ];

    $form['cache'] = [
      '#type' => 'details',
      '#title' => $this->t('Cache'),
      '#collapsible' => TRUE,
      '#open' => TRUE,
      '#weight' => 9,
    ];
    $form['cache']['max_age'] = [
      '#type' => 'number',
      '#title' => $this->t("Cache max age"),
      '#default_value' => isset($config['cache']['cache_max_age']) ? $config['cache']['cache_max_age'] : 0,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values  = $form_state->getValues();

    foreach ($values as $key => $value) {
      $this->setConfigurationValue($key, $value);
    };
  }

  /**
   * Use this function in child block.
   * 
   * @param array $build
   *   Default render array.
   * 
   * @return array
   *   The block content as render array.
   */
  public function blockBuild() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Instanciate Twitter manager.
    $config = $this->getConfiguration();
    if ($application = $config['application'] ?? NULL) {
      $this->twitter->init($application);
    }

    return $this->blockBuild();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // @todo Can it be cached?
    return $this->configuration['cache_max_age'] ?? 0;
  }
}
