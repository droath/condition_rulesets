<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Define the condition rulesets general information.
 */
class ConditionRulesetsGeneral extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.general';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = $this->getWizardEntity($form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getTemporaryValue('wizard');
    $values['id'] = $form_state->getValue('id');
    $values['label'] = $form_state->getValue('label');

    $form_state->setTemporaryValue('wizard', $values);
  }

  /**
   * [getWizardEntity description].
   *
   * @param FormStateInterface $form_state
   *
   * @return [type]
   */
  protected function getWizardEntity(FormStateInterface $form_state) {
    $wizard = $form_state->getTemporaryValue('wizard');

    return $wizard['condition_rulesets'];
  }

}
