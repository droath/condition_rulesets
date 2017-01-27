<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract condition rulesets subform base.
 */
abstract class ConditionRulesetsSubformBase extends FormBase {

  /**
   * Condition machine name.
   *
   * @var string
   */
  protected $machineName;

  /**
   * User tempstore object.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $userTempstore;

  /**
   * Condition user tempstore Id.
   *
   * @var string
   */
  protected $userTempstoreId;

  protected $itemSettings;

  /**
   * Constructor for the condition rulesets subform.
   *
   * @param \Drupal\user\SharedTempStoreFactory $user_tempstore
   *   The user tempstore object.
   */
  public function __construct(SharedTempStoreFactory $user_tempstore) {
    $request = $this->getRequest();
    $this->userTempstore = $user_tempstore;

    $this->currentStep = $request->attributes->has('step')
      ? $request->attributes->get('step')
      : NULL;

    $this->userTempstoreId = $request->query->has('tempstore_id')
      ? $request->query->get('tempstore_id')
      : NULL;

    $this->machineName = $request->attributes->has('machine_name')
      ? $request->attributes->get('machine_name')
      : NULL;

    $cached_values = $this->getTempstoreValues();

    $this->itemSettings = $this->getItemSettings($cached_values['condition_rulesets']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('user.shared_tempstore')
    );
  }

  /**
   * Define the default items settings.
   *
   * @return array
   *   An array of default items settings.
   */
  public function defaultItemSettings() {
    return [
      'name' => NULL,
      'id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $this->getTempstoreValues();
    $form_state->setTemporaryValue('wizard', $cached_values);

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#size' => 30,
      '#required' => TRUE,
      '#default_value' => $this->itemSettings['name'],
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#machine_name' => [
        'source' => ['name'],
        'exists' => [$this, 'itemExists'],
      ],
      '#default_value' => $this->itemSettings['id'],
    ];
    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 250,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    $form_state->setTemporaryValue('wizard', $this->getTempstoreValues());

    if (isset($values['id'])) {
      $item_id = $values['id'];
      $cached_values = $form_state->getTemporaryValue('wizard');

      $entity = $cached_values['condition_rulesets'];
      $property = $this->itemEntityProperty();

      if (!empty($values) && isset($entity->{$property})) {
        $entity->{$property}[$item_id] = $values;
      }

      $this->setTempstoreValues($cached_values);
    }

    $form_state->setRedirect('entity.condition_rulesets.add_step_form', [
      'step' => $this->currentStep,
      'machine_name' => $this->machineName,
    ]);
  }

  /**
   * Check if the item exists.
   *
   * @param string $name
   *   Relationship identifier.
   * @param array $form
   *   An array of the form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return bool
   *   Return TRUE if the item exists; otherwise FALSE.
   */
  public function itemExists($name, array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];
    $property = $this->itemEntityProperty();

    return isset($entity->{$property}[$name]);
  }

  /**
   * Get items settings.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   *
   * @return array
   *   An array of item settings.
   */
  protected function getItemSettings(EntityInterface $entity) {
    return $this->defaultItemSettings();
  }

  /**
   * Get the tempstore values.
   *
   * @return array
   *   An array of the tempstore cached values.
   */
  protected function getTempstoreValues() {
    return $this->sharedTempstore()->get($this->machineName);
  }

  /**
   * Set the tempstore values.
   *
   * @param array $cached_values
   *   An array of the cached values.
   */
  protected function setTempstoreValues(array $cached_values) {
    return $this->sharedTempstore()->set($this->machineName, $cached_values);
  }

  /**
   * Get the shared tempstore.
   *
   * @return \Drupal\user\SharedTempStore
   *   The shared tempstore object.
   */
  protected function sharedTempstore() {
    return $this->userTempstore->get($this->userTempstoreId);
  }

  /**
   * Define the item entity property.
   *
   * @return string
   *   The item entity property name.
   */
  abstract protected function itemEntityProperty();

}
