<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\condition_rulesets\Entity\ConditionRulesetsInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConditionRulesetsRelationshipAdd extends ConditionRulesetsSubformBase {

  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    SharedTempStoreFactory $user_tempstore,
    EntityFieldManagerInterface $entity_field_manaeger
  ) {
    parent::__construct($user_tempstore);

    $this->entityFieldManager = $entity_field_manaeger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('user.shared_tempstore'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.relationship.add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    $form['context'] = [
      '#type' => 'select',
      '#title' => $this->t('Context'),
      '#description' => $this->t('Relationship fields based on the available contexts.'),
      '#options' => $this->buildReferenceOptions($entity),
      '#empty_option' => $this->t('- No context -'),
      '#required' => TRUE,
    ];

    return $form;
  }

  protected function buildReferenceOptions(ConditionRulesetsInterface $condition_rulesets) {
    $options = [];
    $contexts = ['entity:node'];

    foreach ($contexts as $context) {
      list($data_type, $entity_type) = explode(':', $context);

      if ($data_type !== 'entity') {
        continue;
      }
      $mappings = $this->entityFieldManager->getFieldMap();

      foreach ($mappings[$entity_type] as $field_name => $info) {
        if (!isset($info['type']) || $info['type'] !== 'entity_reference') {
          continue;
        }
        $field = FieldStorageConfig::loadByName($entity_type, $field_name);

        if (!isset($field)) {
          continue;
        }
        $target_type = $field->getSetting('target_type');

        $options['entity:' . $target_type . ':' . $field_name] = $field->label();
      }
    }

    //dpm($options);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function itemEntityProperty() {
    return 'relationships';
  }

}
