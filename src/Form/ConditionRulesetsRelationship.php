<?php

namespace Drupal\condition_rulesets\Form;

/**
 * Define the condition rulesets relationships.
 */
class ConditionRulesetsRelationship extends ConditionRulesetsSubformList {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.relationship';
  }

  /**
   * {@inheritdoc}
   */
  protected function itemLabel() {
    return $this->t('Relationships');
  }

  /**
   * {@inheritdoc}
   */
  protected function itemEntityProperty() {
    return 'relationships';
  }

  /**
   * {@inheritdoc}
   */
  protected function actionLinkLabel() {
    return $this->t('Add relationship');
  }

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    return [
      'id' => $this->t('ID'),
      'name' => $this->t('Name'),
      'context' => $this->t('Context'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function routeInfo() {
    return [
      'add' => 'entity.condition_rulesets.relationship.subform_add',
      'remove' => 'entity.condition_rulesets.relationship.subform_remove',
    ];
  }

}
