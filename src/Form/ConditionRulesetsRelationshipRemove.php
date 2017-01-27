<?php

namespace Drupal\condition_rulesets\Form;

/**
 * Define condition rulesets relationship subform remove form.
 */
class ConditionRulesetsRelationshipRemove extends ConditionRulesetsSubformConfirmBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.relationship.delete';
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

}
