<?php

namespace Drupal\condition_rulesets\Plugin\Condition;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\condition_rulesets\ConditionRulesetsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the condition rulesets condition plugin.
 *
 * @Condition(
 *   id = "condition_rulesets",
 *   label = @Translation("Condition rulesets"),
 *   context = {
 *     "entity" = @ContextDefinition("any", required = false, label = @Translation("Entity"))
 *   }
 * )
 */
class ConditionRulesets extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Condition rulesets manager.
   *
   * @var \Drupal\condition_rulesets\ConditionRulesetsManagerInterface
   */
  protected $rulesetsManager;

  /**
   * Construct for the condition rulesets condition plugin.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConditionRulesetsManagerInterface $rulesets_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->rulesetsManager = $rulesets_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('condition_rulesets.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'ruleset' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Condition ruleset: @id', [
      '@id' => $this->configuration['ruleset']['identifier'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if ($form_state instanceof SubformStateInterface) {
      $form_state = $form_state->getCompleteFormState();
    }

    $form['ruleset'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'condition-rulesets-wrapper',
      ],
      '#tree' => TRUE,
    ];

    $context_id = $this->getVisibilityValues(
      ['ruleset', 'required_context'],
      $form_state
    );

    $form['ruleset']['required_context'] = [
      '#type' => 'select',
      '#title' => $this->t('Required context'),
      '#description' => $this->t('Select the required context.'),
      '#options' => $this->getContextOptions($form_state),
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $context_id,
      '#ajax' => [
        'event' => 'change',
        'wrapper' => 'condition-rulesets-wrapper',
        'callback' => [$this, 'ajaxContextCallback'],
      ],
    ];

    if (isset($context_id) && !empty($context_id)) {
      $context = $this->getGatheredContextById($context_id, $form_state);

      if ($context) {
        $form['ruleset']['identifier'] = [
          '#type' => 'select',
          '#title' => $this->t('Ruleset'),
          '#description' => $this->t('Select the condition ruleset to evaluate.'),
          '#options' => $this->rulesetsManager->rulesetOptionsByContext($context),
          '#required' => TRUE,
          '#empty_option' => $this->t('- None -'),
          '#default_value' => $this->configuration['ruleset']['identifier'],
        ];
      }
    }

    $form['context_mapping'] = [
      '#type' => 'value',
      '#value' => isset($context_id) ? ['entity' => $context_id] : [],
    ];

    $form['negate'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Negate the condition'),
      '#default_value' => $this->configuration['negate'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasValue('ruleset')) {

      // Set the ruleset basic configurations.
      $ruleset = array_filter($form_state->getValue('ruleset'));
      $this->configuration['ruleset'] = !empty($ruleset) ? $ruleset : [];
    }

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $ruleset = $this->configuration['ruleset'];

    // Don't allow negating an empty ruleset.
    if (empty($ruleset) && $this->isNegated()) {
      return FALSE;
    }
    $identifier = $ruleset['identifier'];

    return $this->rulesetsManager->evaluateRuleset($identifier, $this->getContext('entity'));
  }

  /**
   * AJAX context callback method.
   *
   * @param array $form
   *   An array of form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of renderable form elements.
   */
  public function ajaxContextCallback(array $form, FormStateInterface $form_state) {
    $plugin_id = $this->getPluginId();

    return $form['visibility'][$plugin_id]['ruleset'];
  }

  /**
   * Get block plugins condition value.
   *
   * Values are first retrieve from the form state values array; otherwise
   * defaults to the configuration array.
   *
   * @param array $parents
   *   An array of parent keys unique to the form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of the plugins condition configurations.
   */
  protected function getVisibilityValues(array $parents, FormStateInterface $form_state) {
    $state_value = $form_state->getValue(
      array_merge(['visibility', $this->getPluginId()], $parents)
    );

    return isset($state_value)
      ? $state_value
      : NestedArray::getValue($this->configuration, $parents);
  }

  /**
   * Gathered entity based contexts.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of entities that are related to the 'entity' data type.
   */
  protected function gatheredEntityContexts(FormStateInterface $form_state) {
    $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];

    // Filter the gathered contexts based on if it's related to an entity.
    return array_filter($contexts, function ($context) {
      if (FALSE === strpos($context->getContextDefinition()->getDataType(), 'entity:')) {
        return FALSE;
      }

      return TRUE;
    });
  }

  /**
   * Get context options.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of contexts keyed by their identifier.
   */
  protected function getContextOptions(FormStateInterface $form_state) {
    $options = [];

    foreach ($this->gatheredEntityContexts($form_state) as $context_id => $context) {
      $options[$context_id] = $context->getContextDefinition()->getLabel();
    }

    return $options;
  }

  /**
   * Get gathered context by ID.
   *
   * @param string $context_id
   *   The context identifier.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Component\Plugin\Context\Context|bool
   *   Return the context object; otherwise FALSE.
   */
  protected function getGatheredContextById($context_id, FormStateInterface $form_state) {
    $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];

    return isset($contexts[$context_id]) ? $contexts[$context_id] : FALSE;
  }

}
