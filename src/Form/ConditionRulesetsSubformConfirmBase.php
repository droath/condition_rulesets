<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\condition_rulesets\SharedTempstoreTrait;

/**
 * Abstract condition rulesets subform confirm base.
 */
abstract class ConditionRulesetsSubformConfirmBase extends ConfirmFormBase {

  use SharedTempstoreTrait;

  /**
   * Construct for condition rulesets subform confirm base.
   */
  public function __construct() {
    $this->setSharedTempstore($this->getTempstoreId(), $this->getMachineName());
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $item = $this->getSubFormItem();

    return $this->t('Are you sure you want to remove %name?', [
      '%name' => $item['name'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Remove');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url($this->returnRoute(), [
      'step' => $this->getStep(),
      'machine_name' => $this->getMachineName(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $item = $this->getSubFormItem();

    drupal_set_message(
      $this->t('@label %name has been removed.', [
        '%name' => $item['name'],
        '@label' => $this->itemLabel(),
      ]
    ));
    $this->removeSubFormItem();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Get the subform item based on identifier.
   *
   * @return array
   *   An array of subform item values.
   */
  protected function getSubFormItem() {
    $items = $this->getSubFormItems();
    $identifier = $this->getItemIdentifier();

    if (!isset($items[$identifier])) {
      return [];
    }

    return $items[$identifier];
  }

  /**
   * Remove the subform item based on identifier.
   */
  protected function removeSubFormItem() {
    $values = $this->getTempstoreValues();

    $entity = &$values['condition_rulesets'];
    $property = $this->itemEntityProperty();

    if (isset($entity->{$property})) {
      unset($entity->{$property}[$this->getItemIdentifier()]);

      $this->setTempstoreValues($values);
    }
  }

  /**
   * Get all entity property items.
   *
   * @return array
   *   An array of the entity items based on the property.
   */
  protected function getSubFormItems() {
    $values = $this->getTempstoreValues();

    if (!isset($values['condition_rulesets'])) {
      return [];
    }
    $entity = $values['condition_rulesets'];
    $property = $this->itemEntityProperty();

    if (!isset($entity->{$property})) {
      return [];
    }

    $items = $entity->{$property};

    return $items;
  }

  /**
   * Get shared tempstore identifier.
   *
   * @return string|bool
   *   The tempstore identifier; otherwise FALSE.
   */
  protected function getTempstoreId() {
    $request = $this->getRequest();

    if (!$request->query->has('tempstore_id')) {
      return FALSE;
    }

    return $request->query->get('tempstore_id');
  }

  /**
   * Get wizard steps machine name.
   *
   * @return string|bool
   *   The wizard steps machine name; otherwise FALSE.
   */
  protected function getMachineName() {
    $request = $this->getRequest();

    if (!$request->attributes->has('machine_name')) {
      return FALSE;
    }

    return $request->attributes->get('machine_name');
  }

  /**
   * Get subform item identifier.
   *
   * @return string|bool
   *   The subform item identifier; otherwise FALSE.
   */
  protected function getItemIdentifier() {
    $request = $this->getRequest();

    if (!$request->attributes->has('id')) {
      return FALSE;
    }

    return $request->attributes->get('id');
  }

  /**
   * Get subform current step.
   *
   * @return string|bool
   *   The subform current step; otherwise FALSE.
   */
  protected function getStep() {
    $request = $this->getRequest();

    if (!$request->attributes->has('step')) {
      return FALSE;
    }

    return $request->attributes->get('step');
  }

  /**
   * Define a return route.
   *
   * Used for a submit or cancel action.
   *
   * @return string
   *   A valid route name.
   */
  protected function returnRoute() {
    return 'entity.condition_rulesets.edit_form';
  }

  /**
   * Define the item label.
   *
   * @return string
   *   The item label.
   */
  abstract protected function itemLabel();

  /**
   * Define the entity items property name.
   *
   * @return string
   *   The entity items property name.
   */
  abstract protected function itemEntityProperty();

}
