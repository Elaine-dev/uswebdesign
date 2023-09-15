<?php

namespace Drupal\lingotek\Plugin\Action;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\lingotek\Exception\LingotekApiException;
use Drupal\lingotek\Exception\LingotekDocumentArchivedException;
use Drupal\lingotek\Exception\LingotekDocumentLockedException;
use Drupal\lingotek\Exception\LingotekDocumentNotFoundException;
use Drupal\lingotek\Exception\LingotekPaymentRequiredException;
use Drupal\lingotek\Exception\LingotekProcessedWordsLimitException;

/**
 * Request Lingotek translation of a content entity for one language.
 *
 * @Action(
 *   id = "entity:lingotek_request_translation_action",
 *   action_label = @Translation("Request @entity_label translation to Lingotek for @language"),
 *   category = "Lingotek",
 *   deriver = "Drupal\lingotek\Plugin\Action\Derivative\ContentEntityLingotekActionDeriver",
 * )
 */
class RequestTranslationLingotekAction extends LingotekContentEntityConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $result = FALSE;

    $entityTypeBundleInfo = \Drupal::service('entity_type.bundle.info');
    $bundleInfos = $entityTypeBundleInfo->getBundleInfo($entity->getEntityTypeId());
    if (!$entity->getEntityType()->isTranslatable() || !$bundleInfos[$entity->bundle()]['translatable']) {
      $this->messenger()->addWarning(t('Cannot request @type %label. That @bundle_label is not enabled for translation.',
        ['@type' => $bundleInfos[$entity->bundle()]['label'], '%label' => $entity->label(), '@bundle_label' => $entity->getEntityType()->getBundleLabel()]));
      return FALSE;
    }
    /** @var \Drupal\lingotek\LingotekConfigurationServiceInterface $lingotek_configuration */
    $lingotek_configuration = \Drupal::service('lingotek.configuration');
    if (!$lingotek_configuration->isEnabled($entity->getEntityTypeId(), $entity->bundle())) {
      $this->messenger()->addWarning(t('Cannot request @type %label. That @bundle_label is not enabled for Lingotek translation.',
        ['@type' => $bundleInfos[$entity->bundle()]['label'], '%label' => $entity->label(), '@bundle_label' => $entity->getEntityType()->getBundleLabel()]));
      return FALSE;
    }

    $configuration = $this->getConfiguration();
    $langcode = $configuration['language'];

    $language = ConfigurableLanguage::load($langcode);
    if (!$lingotek_configuration->isLanguageEnabled($language)) {
      $this->messenger()->addWarning(t('Cannot request language @language (%langcode). That language is not enabled for Lingotek translation.',
        ['@language' => $language->getName(), '%langcode' => $langcode]));
      return FALSE;
    }

    try {
      $locale = $this->languageLocaleMapper->getLocaleForLangcode($langcode);
      $result = $this->translationService->addTarget($entity, $locale);
    }
    catch (LingotekPaymentRequiredException $exception) {
      $this->messenger()->addError(t('Community has been disabled. Please contact support@lingotek.com to re-enable your community.'));
    }
    catch (LingotekDocumentNotFoundException $exc) {
      $this->messenger()
        ->addError(t('Document @entity_type %title was not found. Please upload again.', [
          '@entity_type' => $entity->getEntityTypeId(),
          '%title' => $entity->label(),
        ]));
    }
    catch (LingotekDocumentArchivedException $exception) {
      $this->messenger()->addError(t('Document @entity_type %title has been archived. Uploading again.', [
        '@entity_type' => $entity->getEntityTypeId(),
        '%title' => $entity->label(),
      ]));
      $plugin_manager = \Drupal::service('plugin.manager.action');
      $plugin_manager
        ->createInstance(str_replace('lingotek_request_translation_action', 'lingotek_upload_action', $this->getPluginId()), $this->configuration)
        ->execute($entity);
    }
    catch (LingotekDocumentLockedException $exception) {
      $this->messenger()->addError(t('Document @entity_type %title has a new version. The document id has been updated for all future interactions. Please try again.', ['@entity_type' => $entity->getEntityTypeId(), '%title' => $entity->label()]));
    }
    catch (LingotekProcessedWordsLimitException $exception) {
      $this->messenger()->addError(t('Processed word limit exceeded. Please contact your local administrator or Lingotek Client Success (<a href=":link">@mail</a>) for assistance.', [':link' => 'mailto:sales@lingotek.com', '@mail' => 'sales@lingotek.com']));
    }
    catch (LingotekApiException $exception) {
      $this->messenger()
        ->addError(t('The request for @entity_type %title translation failed. Please try again.', [
          '@entity_type' => $entity->getEntityTypeId(),
          '@langcode' => $langcode,
          '%title' => $entity->label(),
        ]));
    }
    return $result;
  }

}
