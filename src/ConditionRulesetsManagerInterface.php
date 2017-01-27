<?php

namespace Drupal\condition_rulesets;

use Drupal\Core\Plugin\Context\Context;

/**
 * Interface \Drupal\condition_rulesets\ConditionRulesetsInterface.
 */
interface ConditionRulesetsManagerInterface {

  public function evaluateRuleset($identifier, Context $context);

}
