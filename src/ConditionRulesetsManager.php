<?php

namespace Drupal\condition_rulesets;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;

/**
 * Define the condition rulesets manager.
 */
class ConditionRulesetsManager implements ConditionRulesetsManagerInterface {

  protected $entityTypeManager;

  protected $entityTypeStorage;

  /**
   * Constructor for the condition rulesets manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeStorage = $this->entityTypeManager->getStorage('condition_rulesets');
  }

  /**
   * Load rulesets based on context.
   *
   * @param \Drupal\Core\Plugin\Context\Context $context
   *   The context object that's required for the ruleset.
   *
   * @return array
   *   An array of condition rulesets that match the context.
   */
  public function loadRulesetsByContext(Context $context) {
    $defintion = $context->getContextDefinition();

    return $this->entityTypeStorage
      ->loadByProperties([
        'required_context' => $defintion->getDataType(),
      ]);
  }

  /**
   * Get rulesets options based on context.
   *
   * @param \Drupal\Core\Plugin\Context\Context $context
   *   The context object that's required for the ruleset.
   *
   * @return array
   *   An array of condition rulesets options that match the context.
   */
  public function rulesetOptionsByContext(Context $context) {
    $options = [];

    foreach ($this->loadRulesetsByContext($context) as $name => $ruleset) {
      if (!isset($ruleset->label)) {
        continue;
      }

      $options[$name] = $ruleset->label;
    }

    return $options;
  }

  /**
   * Evaluate the ruleset conditions.
   *
   * @param string $identifier
   *   The ruleset identifier.
   * @param \Drupal\Core\Plugin\Context\Context $context
   *   The context object that's required for the ruleset.
   *
   * @return bool
   *   Return TRUE if ruleset conditions met the requirements; otherwise FALSE.
   */
  public function evaluateRuleset($identifier, Context $context) {
    $ruleset = $this->entityTypeStorage->load($identifier);

    // Return FALSE if ruleset wasn't found.
    if (!isset($ruleset)) {
      return FALSE;
    }

    foreach ($ruleset->conditions as $name => $condition) {

      dpm($condition);

    }

    // check evaluation rule
    // $ruleset->evaluation_rule;

    return TRUE;
  }
}
