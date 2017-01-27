<?php

namespace Drupal\condition_rulesets\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Define the condition rulesets entity configuration.
 *
 * @ConfigEntityType(
 *   id = "condition_rulesets",
 *   label = @Translation("Condition Rulesets"),
 *   admin_permission = "administer condition rulesets",
 *   handlers = {
 *     "list_builder" = "\Drupal\condition_rulesets\Controller\ConditionRulesetsListBuilder",
 *     "form" = {
 *       "delete" = "\Drupal\condition_rulesets\Form\ConditionRulesetsDeleteForm"
 *     },
 *     "wizard" = {
 *       "add" = "\Drupal\condition_rulesets\Wizard\ConditionRulesetsWizardAdd",
 *       "edit" = "\Drupal\condition_rulesets\Wizard\ConditionRulesetsWizardEdit"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "collection" = "/admin/config/system/condition_rulesets",
 *     "canonical" = "/admin/config/system/condition_rulesets/{condition_rulesets}",
 *     "edit-form" = "/admin/config/system/condition_rulesets/{machine_name}/{step}",
 *     "delete-form" = "/admin/config/system/condition_rulesets/{condition_rulesets}/delete"
 *   }
 * )
 */
class ConditionRulesets extends ConfigEntityBase implements ConditionRulesetsInterface {

  use StringTranslationTrait;

  /**
   * Condition rulesets ID.
   *
   * @var string
   */
  public $id;

  /**
   * Condition rulesets label.
   *
   * @var string
   */
  public $label;

  /**
   * Condition rulesets required context.
   *
   * @var string
   */
  public $required_context;

  /**
   * Condition rulesets conditions.
   *
   * @var array
   */
  public $conditions = [];

  /**
   * Condition rulesets relationships.
   *
   * @var array
   */
  public $relationships = [];

  /**
   * Condition rulesets condition evaluation rule.
   *
   * @var string
   */
  public $evaluation_rule;

  /**
   * {@inheritdoc}
   */
  public function getRequiredContext() {
    return new Context(
      new ContextDefinition($this->required_context, $this->t('Required context'))
    );
  }

  /**
   * Get required context entity type ID.
   *
   * @return string
   *   The context entity type id.
   */
  public function getContextEntityTypeId() {
    return substr($this->required_context, 7);
  }

  public function availableContextOptions() {

  }

  protected function entityFieldManager() {
    return \Drupal::service('entity_type.manager');
  }
}
