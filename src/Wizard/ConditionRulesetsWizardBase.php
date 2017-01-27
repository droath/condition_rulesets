<?php

namespace Drupal\condition_rulesets\Wizard;

use Drupal\ctools\Wizard\EntityFormWizardBase;

/**
 * Define the condition rulesets form wizard base.
 */
abstract class ConditionRulesetsWizardBase extends EntityFormWizardBase {

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return 'condition_rulesets';
  }

  /**
   * {@inheritdoc}
   */
  public function getWizardLabel() {
    return $this->t('Condition rulesets');
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineLabel() {
    return $this->t('Label');
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    $config_entity = $cached_values['condition_rulesets'];

    $steps = [
      'general' => [
        'form' => '\Drupal\condition_rulesets\Form\ConditionRulesetsGeneral',
        'title' => $this->t('General'),
      ],
      'context' => [
        'form' => '\Drupal\condition_rulesets\Form\ConditionRulesetsContext',
        'title' => $this->t('Context'),
      ],
      'relationship' => [
        'form' => '\Drupal\condition_rulesets\Form\ConditionRulesetsRelationship',
        'title' => $this->t('Relationship'),
      ],
      'condition' => [
        'form' => '\Drupal\condition_rulesets\Form\ConditionRulesetsCondition',
        'title' => $this->t('Condition'),
      ],
    ];

    return $steps;
  }

  /**
   * {@inheritdoc}
   */
  public function exists() {
    return '\Drupal\condition_rulesets\Entity\ConditionRulesets::load';
  }

}
