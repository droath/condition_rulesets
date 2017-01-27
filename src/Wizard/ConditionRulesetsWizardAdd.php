<?php

namespace Drupal\condition_rulesets\Wizard;

/**
 * Define the condition rulesets form wizard add functionality.
 */
class ConditionRulesetsWizardAdd extends ConditionRulesetsWizardBase {

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'entity.condition_rulesets.add_step_form';
  }

}
