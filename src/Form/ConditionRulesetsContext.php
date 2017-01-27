<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the condition rulesets context.
 */
class ConditionRulesetsContext extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity type bundle information.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructor for condition rulesets context.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'condition_rulesets.context';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    $required_context = $form_state->hasValue('required_context')
      ? $form_state->getValue('required_context')
      : $entity->required_context;

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'condition-rulesets-context',
      ],
    ];
    $form['container']['required_context'] = [
      '#type' => 'select',
      '#title' => $this->t('Required context'),
      '#options' => $this->getEntityContextOptions(),
      '#required' => TRUE,
      '#default_value' => $required_context,
      '#ajax' => [
        'event' => 'change',
        'method' => 'replace',
        'callback' => [$this, 'ajaxContextChange'],
        'wrapper' => 'condition-rulesets-context',
      ],
    ];

    if (!empty($required_context)) {
      $options = $this->getContextBundleOptions($required_context);

      if (count($options) > 1) {
        $form['container']['context_bundles'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Context bundles'),
          '#description' => $this->t('Select allow'),
          '#options' => $options,
        ];
      }
    }

    return $form;
  }

  protected function getContextBundleOptions($context_name) {
    list($data_type, $entity_type_id) = explode(':', $context_name);

    $options = [];
    if ($data_type === 'entity') {
      $bundles = $this->entityTypeBundleInfo
        ->getBundleInfo($entity_type_id);

      foreach ($bundles as $name => $info) {
        if (!isset($info['label'])) {
          continue;
        }

        $options[$name] = $info['label'];
      }
    }

    return $options;
  }

  /**
   * Ajax context change.
   *
   * @param array $form
   *   An array of form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of form elements.
   */
  public function ajaxContextChange(array $form, FormStateInterface $form_state) {
    return $form['container'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    $entity->required_context = $form_state->getValue('required_context');
  }

  /**
   * Get entity context options.
   *
   * @return array
   *   An array of entity contexts.
   */
  protected function getEntityContextOptions() {
    $options = &drupal_static(__METHOD__, []);

    if (empty($options)) {
      foreach ($this->entityTypeManager->getDefinitions() as $type => $entity) {
        $options[$entity->getGroup()]['entity:' . $type] = $entity->getLabel();
      }
    }

    return array_reverse($options);
  }

}
