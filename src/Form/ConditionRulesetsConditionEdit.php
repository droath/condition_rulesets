<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Entity\EntityInterface;

/**
 * Define the condition rulesets condition edit form.
 */
class ConditionRulesetsConditionEdit extends ConditionRulesetsConditionAdd {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.condition.edit';
  }

  /**
   * {@inheritdoc}
   */
  protected function getItemSettings(EntityInterface $entity) {
    $identifier = $this->getItemIdentifier();

    return isset($identifier) && isset($entity->conditions[$identifier])
      ? $entity->conditions[$identifier]
      : $this->defaultItemSettings();
  }

  /**
   * Get item identifier.
   *
   * @return string
   *   The item identifier.
   */
  protected function getItemIdentifier() {
    $request = $this->getRequest();

    return $request->attributes->has('id')
      ? $request->attributes->get('id')
      : NULL;
  }

}
