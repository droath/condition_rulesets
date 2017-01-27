<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the condition rulesets conditions.
 */
class ConditionRulesetsCondition extends ConditionRulesetsSubformList {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.condition';
  }

  public function __construct($field_widget_manager, $typed_data_manager) {
    $this->fieldWidgetManager = $field_widget_manager;
    $this->typedDataManager = $typed_data_manager;
  }

  public static function create(ContainerInterface $container) {
   return new static (
      $container->get('plugin.manager.field.widget'),
      $container->get('typed_data_manager')
   );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    $field_manager = \Drupal::service('entity_field.manager');
    // $base_fields = $field_manager->getBaseFieldDefinitions('node');
    // dpm($base_fields);

    // $config = FieldStorageConfig::loadByName('node', 'field_disclosure');
    // dpm($config);

    // $node = \Drupal\node\Entity\Node::load(11);
    // dpm($node);

    // $field_config = \Drupal\field\Entity\FieldConfig::loadByName('node', 'article', 'field_disclosure');
    // dpm($field_config->getLabel());

    // $field_storage = $field_manager->getFieldStorageDefinitions('node');
    // $field_definition = BaseFieldDefinition::createFromFieldStorageDefinition($field_storage['nid']);
    // dpm($field_definition);

    // dpm($field_definition->getPropertyDefinitions());

    // $widget = $this->fieldWidgetManager->getInstance([
    //   'field_definition' => $field_definition,
    //   'form_mode' => 'default',
    //   'prepare' => FALSE,
    //   'configuration' => [
    //     'type' => 'hidden',
    //     'settings' => [],
    //     'third_party_settings' => [],
    //   ],
    // ]);

    // dpm($widget);

    // $items = \Drupal\Core\Field\FieldItemList::createInstance($field_definition, 'nid');
    // dpm($items);
    // $items = $node->get('nid');

    // dpm($items);
    // dpm($widget);

    // $list = $this->typedDataManager->createListDataDefinition('entity');
    // dpm($list);

    // $form += array('#parents' => array());
    // $widget_form = $widget->form($items, $form, $form_state);

    // dpm($widget_form);

    // dpm($config);
    // dpm($field_definition);

    if (!empty($entity->conditions) && count($entity->conditions) > 1) {
      $form['evaluation_rule'] = [
        '#type' => 'select',
        '#title' => $this->t('Evaluation rule'),
        '#description' => $this->t('Select how multiple conditions are handled.'),
        '#options' => [
          'pass_one' => $this->t('One condition must pass'),
          'pass_all' => $this->t('All conditions must pass'),
        ],
        '#default_value' => isset($entity->evaluation_rule)
          ? $entity->evaluation_rule
          : 'pass_all',
      ];
    }
    else {
      $form['evaluation_rule'] = [
        '#type' => 'value',
        '#value' => 'pass_one',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_value = $form_state->getTemporaryValue('wizard');
    $entity = $cached_value['condition_rulesets'];

    $entity->evaluation_rule = $form_state->getValue('evaluation_rule');
  }

  /**
   * {@inheritdoc}
   */
  protected function itemLabel() {
    return $this->t('Conditions');
  }

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    return [
      'id' => $this->t('ID'),
      'name' => $this->t('Name'),
      // 'context' => $this->t('Context'),
      'field_name' => $this->t('Field name'),
      'settings' => $this->t('Settings'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function itemEntityProperty() {
    return 'conditions';
  }

  /**
   * {@inheritdoc}
   */
  protected function actionLinkLabel() {
    return $this->t('Add condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function routeInfo() {
    return [
      'add' => 'entity.condition_rulesets.condition.subform_add',
      'edit' => 'entity.condition_rulesets.condition.subform_edit',
      'remove' => 'entity.condition_rulesets.condition.subform_remove',
    ];
  }

}
