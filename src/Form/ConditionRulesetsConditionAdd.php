<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\condition_rulesets\EntityFieldWidgetRenderInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConditionRulesetsConditionAdd extends ConditionRulesetsSubformBase {

  /**
   * [$entityFieldWidgetRender description].
   *
   * @var [type]
   */
  protected $fieldWidgetRender;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    SharedTempStoreFactory $user_tempstore,
    EntityFieldWidgetRenderInterface $field_widget_render
  ) {
    parent::__construct($user_tempstore);

    $this->fieldWidgetRender = $field_widget_render;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('user.shared_tempstore'),
      $container->get('condition_rulesets.entity.field_widget.render')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.relationship.add';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultItemSettings() {
    return parent::defaultItemSettings() + [
      'context' => NULL,
      'field_name' => NULL,
      'settings' => [
        'widget' => [
          'value' => NULL,
        ],
      ],
    ];
  }

  protected function getItemSetting($name, FormStateInterface $form_state) {
    return $form_state->hasValue($name)
      ? $form_state->getValue($name)
      : NestedArray::getValue($this->itemSettings, [$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    if (!empty($entity->relationships)) {
      $options = [];//$entity->loadRelationshipOptions();

      $form['context'] = [
        '#type' => 'select',
        '#title' => $this->t('Context'),
        '#options' => $options,
        '#default_value' => $this->itemSettings['context'],
      ];
    }
    else {
      // $form['context'] = [
      //   '#type' => 'value',
      //   '#value' => $entity->getRequiredContext(),
      // ];
    }

    // @todo: allow this to get changed based on relationship context; this is
    // the default
    $context = $entity->getRequiredContext();

    $options = $this->fieldWidgetRender->entityFieldOptionsByContext($context);
    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#options' => $options,
      '#default_value' => $this->itemSettings['field_name'],
      '#required' => TRUE,
      '#ajax' => [
        'event' => 'change',
        'method' => 'replace',
        'wrapper' => 'condition-rulesets-settings',
        'callback' => [$this, 'ajaxChangeField'],
      ],
    ];
    $field_name = $this->getItemSetting('field_name', $form_state);

    if (isset($field_name) && !empty($field_name)) {
      $settings = $this->getItemSetting('settings', $form_state);

      $element = $this->fieldWidgetRender
        ->renderWidgetElement($context, $field_name, $settings, $form_state);

      $form['settings'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Widget settings'),
        '#tree' => TRUE,
      ] + $element;
    }

    $form['settings']['#prefix'] = '<div id="condition-rulesets-settings">';
    $form['settings']['#suffix'] = '</div>';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    if (!isset($values['field_name']) || !isset($values['settings'])) {
      return;
    }
    $settings = $values['settings'];
    $field_name = $values['field_name'];

    if (empty($entity->conditions)) {
      return;
    }

    $context = isset($entity->conditions['context'])
      ? $entity->conditions['context']
      : $entity->getRequiredContext();

    $this->fieldWidgetRender->removeEmptyWidgetValues($context, $field_name, $settings);

    $entity->conditions = $settings;

    $form_state->setTemporaryValue('wizard', $cached_values);
  }

  /**
   * [ajaxChangeField description].
   *
   * @param array $form
   * @param FormStateInterface $form_state
   *
   * @return [type]
   */
  public function ajaxChangeField(array $form, FormStateInterface $form_state) {
    return $form['settings'];
  }

  /**
   * {@inheritdoc}
   */
  protected function itemEntityProperty() {
    return 'conditions';
  }

}
