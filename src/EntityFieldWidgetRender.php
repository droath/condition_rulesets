<?php

namespace Drupal\condition_rulesets;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\Context;

/**
 * Define the entity field widget render service.
 */
class EntityFieldWidgetRender implements EntityFieldWidgetRenderInterface {

  protected $entityTypeManager;

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  protected $entityTypeBundleInfo;

  protected $entityFieldWidgetManager;

  /**
   * Construct for the field widget render service.
   */
  public function __construct(
    CacheFactoryInterface $cache_backend,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle,
    EntityFieldManagerInterface $entity_field_manager,
    PluginManagerInterface $entity_field_widget_manager
  ) {
    $this->cacheBackend = $cache_backend->get('entity');
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityFieldWidgetManager = $entity_field_widget_manager;
  }

  public function entityBundleFields($entity_type_id) {
    $properties = [];

    foreach ($this->getFieldMapByEntity($entity_type_id) as $field_name => $field_info) {
      $bundles = $field_info['bundles'];
      $bundle_name = array_pop($bundles);

      $properties[$bundle_name][$field_name] = [
        'other_bundles' => $bundles,
      ];
    }

    return $properties;
  }

  public function entityFieldItemsByContext(Context $context) {
    $entity_type_id = $this->getEntityTypeIdFromContext($context);

    return $this->entityFieldItems($entity_type_id);
  }

  public function entityFieldOptionsByContext(Context $context) {
    $options = [];
    $entity_type_id = $this->getEntityTypeIdFromContext($context);

    foreach ($this->entityFieldItems($entity_type_id) as $field_name => $item) {
      if (!$item instanceof FieldItemListInterface) {
        continue;
      }
      $definition = $item->getFieldDefinition();

      $options[$field_name] = $definition->getLabel();
    }

    return $options;
  }

  protected function entityFieldItems($entity_type_id) {
    $cid = 'entity_field_items:' . $entity_type_id;

    if ($cache = $this->cacheBackend->get($cid)) {
      $items = $cache->data;
    }
    else {
      foreach ($this->entityBundleFields($entity_type_id) as $bundle_name => $fields) {
        $entity = $this->createEntity($entity_type_id, $bundle_name);

        if ($entity instanceof FieldableEntityInterface) {
          foreach (array_keys($fields) as $field_name) {
            if (!$entity->hasField($field_name)) {
              continue;
            }

            $items[$field_name] = $entity->get($field_name);
          }
        }
      }

      $this->cacheBackend->set($cid, $items, Cache::PERMANENT, ['entity_field_items']);
    }

    return $items;
  }

  public function renderWidgetElement(Context $context, $field_name, array $settings, FormStateInterface $form_state) {
    $items = $this->entityFieldItemsByContext($context);

    if (!isset($items[$field_name])) {
      return [];
    }
    $field_item = $items[$field_name];
    $field_definition = $field_item->getFieldDefinition();

    // Retrieve the widget instance based on the field definition.
    $widget = $this->entityFieldWidgetManager->getInstance([
      'field_definition' => $field_definition,
      'form_mode' => 'default',
      'prepare' => FALSE,
      'configuration' => [
        'type' => 'hidden',
        'settings' => [],
        'third_party_settings' => [],
      ],
    ]);

    $form = ['#parents' => []];

    // Set the field item value based on the provided settings.
    $this->setFieldItemValue($field_item, $settings);

    // Render the widget based on the provided field item.
    $widget_element = $widget->form($field_item, $form, $form_state);

    // Remove the parents properties on the field widget element, as it causes
    // problems with capturing the widget value, as we're saving the value
    // independently from the widget.
    unset($widget_element['#parents']);
    unset($widget_element['widget']['#parents']);

    return $widget_element;
  }

  public function removeEmptyWidgetValues(Context $context, $field_name, array &$item_values) {
    $items = $this->entityFieldItemsByContext($context);

    if (!isset($items[$field_name])) {
      return;
    }
    $field_item = $items[$field_name];

    $this->setFieldItemValue($field_item, $item_values);

    foreach ($field_item as $delta => $item) {
      if (isset($item_values['widget'][$delta]) && $item->isEmpty()) {
        unset($item_values['widget'][$delta]);
      }
    }
  }

  protected function cleanWidgetValues(array &$values) {
    if (isset($values['widget'])) {
    }

    return $this;
  }

  /**
   * Set field item values.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field_item
   *   The field item object.
   * @param array $settings
   *   An array that contains the widget item values.
   */
  protected function setFieldItemValue(FieldItemListInterface $field_item, array $item_values) {
    if (isset($item_values['widget'])) {
      $values = $item_values['widget'];

      if (isset($values['add_more'])) {
        unset($values['add_more']);
      }

      if (!empty($values)) {
        $field_item->setValue($values);
      }
    }

    return $this;
  }

  protected function getEntityTypeIdFromContext(Context $context) {
    $definition = $context->getContextDefinition();
    $data_type = $definition->getDataType();

    return isset($data_type) ? substr($data_type, 7) : FALSE;
  }

  protected function getFieldMapByEntity($entity_type_id) {
    $field_maps = $this->entityFieldManager->getFieldMap($entity_type_id);

    return $field_maps[$entity_type_id] ?: [];
  }

  protected function createEntity($entity_type_id, $bundle) {
    $entity_storage = $this->entityTypeManager->getStorage($entity_type_id);
    $bundle_key = $entity_storage->getEntityType()->getKey('bundle');

    return isset($bundle) && !empty($bundle_key)
      ? $entity_storage->create([$bundle_key => $bundle])
      : $entity_storage->create();
  }
}
