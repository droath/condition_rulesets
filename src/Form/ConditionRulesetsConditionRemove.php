<?php

namespace Drupal\condition_rulesets\Form;

/**
 * Define condition rulesets condition subform remove form.
 */
class ConditionRulesetsConditionRemove extends ConditionRulesetsSubformConfirmBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.condition.delete';
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
  protected function itemEntityProperty() {
    return 'conditions';
  }

}
