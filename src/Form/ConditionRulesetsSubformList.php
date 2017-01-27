<?php

namespace Drupal\condition_rulesets\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Abstract condition rulesets subform list.
 */
abstract class ConditionRulesetsSubformList extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Render the subform link.
    $form['link'] = $this->buildActionLink($form_state);

    // Render the subform list table.
    $form['table'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader() + [
        'operations' => $this->t('Operations'),
      ],
      '#rows' => $this->buildRows($form_state),
      '#empty' => $this->buildEmptyText(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Intentionally left empty as list aren't required to save items.
  }

  /**
   * Define table list headers.
   *
   * @return array
   *   An array of headers.
   */
  protected function buildHeader() {
    return [
      'id' => $this->t('ID'),
      'name' => $this->t('Name'),
    ];
  }

  /**
   * Define table list action link label.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translatable markup object.
   */
  protected function actionLinkLabel() {
    return $this->t('Add item');
  }

  /**
   * Define table list action link element.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   A renderable array of the action link.
   */
  protected function buildActionLink(FormStateInterface $form_state) {
    $parent_form = $form_state->getFormObject();
    $cached_values = $form_state->getTemporaryValue('wizard');

    return [
      '#type' => 'link',
      '#title' => $this->actionLinkLabel(),
      '#attributes' => $this->actionLinkAttributes(),
      '#url' => Url::fromRoute($this->getRouteInfo('add'), [
        'step' => $parent_form->getStep($cached_values),
        'machine_name' => $parent_form->getMachineName(),
        'tempstore_id' => $parent_form->getTempstoreId(),
      ]),
    ];
  }

  /**
   * Define table list action link attributes.
   *
   * @return array
   *   An array of action link attributes.
   */
  protected function actionLinkAttributes() {
    return array_merge_recursive([
      'class' => [
        'button',
        'button-action',
        'button--primary',
        'button--small',
      ],
    ], $this->ajaxModalAttributes());
  }

  /**
   * Define table empty text.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translatable markup object.
   */
  protected function buildEmptyText() {
    return $this->t('No @item_label have been defined.', [
      '@item_label' => strtolower($this->itemLabel()),
    ]);
  }

  /**
   * Build the table rows based on the entity.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of item rows.
   */
  protected function buildRows(FormStateInterface $form_state) {
    $rows = [];
    $property = $this->itemEntityProperty();

    $cached_values = $form_state->getTemporaryValue('wizard');
    $entity = $cached_values['condition_rulesets'];

    if (isset($entity->{$property})
      && !empty($entity->{$property})) {

      foreach ($entity->{$property} as $index => $info) {
        if (!is_array($info)) {
          continue;
        }

        // @todo: enhance the output for settings.
        foreach ($info as $info_property => &$value) {
          if (is_array($value)) {
            $value = json_encode($value);
          }
        }
        $rows[$index] = $info;

        if ($operations = $this->buildOperation($info, $form_state)) {
          $rows[$index][] = [
            'data' => $operations,
          ];
        }
      }
    }

    return $rows;
  }

  /**
   * Build the operations.
   *
   * @param array $item
   *   An array of the item values.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   A renderable array of the operation element.
   */
  protected function buildOperation(array $item, FormStateInterface $form_state) {
    return [
      '#type' => 'dropbutton',
      '#links' => $this->buildOperationLinks($item, $form_state),
    ];
  }

  /**
   * Build the operations links.
   *
   * @param array $item
   *   An array of the item values.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of operation links.
   */
  protected function buildOperationLinks(array $item, FormStateInterface $form_state) {
    $links = [];

    $parent_form = $form_state->getFormObject();
    $cached_values = $form_state->getTemporaryValue('wizard');

    if (isset($item['id'])) {

      // Provide the edit action link if the route has been defined.
      if ($this->hasRouteInfo('edit')) {
        $links['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute($this->getRouteInfo('edit'), [
            'id' => $item['id'],
            'step' => $parent_form->getStep($cached_values),
            'machine_name' => $parent_form->getMachineName(),
            'tempstore_id' => $parent_form->getTempstoreId(),
          ]),
          'attributes' => $this->ajaxModalAttributes(),
        ];
      }

      // Provide the remove action link if the route has been defined.
      if ($this->hasRouteInfo('remove')) {
        $links['remove'] = [
          'title' => $this->t('Remove'),
          'url' => Url::fromRoute($this->getRouteInfo('remove'), [
            'id' => $item['id'],
            'step' => $parent_form->getStep($cached_values),
            'machine_name' => $parent_form->getMachineName(),
            'tempstore_id' => $parent_form->getTempstoreId(),
          ]),
          'attributes' => $this->ajaxModalAttributes(),
        ];
      }
    }

    return $links;
  }

  /**
   * AJAX modal attributes.
   *
   * @return array
   *   An array of Ajax modal attributes.
   */
  protected function ajaxModalAttributes() {
    return [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => 800,
      ]),
    ];
  }

  /**
   * Get related route names for action.
   *
   * @param string $name
   *   The route action name.
   *
   * @return string
   *   The route name; otherwise NULL.
   */
  protected function getRouteInfo($name) {
    $route_info = $this->routeInfo();

    if (!$this->hasRouteInfo($name)) {
      throw new \Exception(
        $this->t('Missing route @name for subform list.',
        ['@name' => $name]
      ));
    }

    return $route_info[$name];
  }

  /**
   * Has related route name for action.
   *
   * @param string $name
   *   The route action name.
   *
   * @return bool
   *   Return TRUE if route action exists; otherwise FALSE.
   */
  protected function hasRouteInfo($name) {
    return isset($this->routeInfo()[$name]);
  }

  /**
   * Define item label.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translatable markup object.
   */
  protected function itemLabel() {
    return $this->t('Items');
  }

  /**
   * Define the route information.
   *
   * @return array
   *   An array of route information.
   */
  abstract protected function routeInfo();

  /**
   * Define the list entity property.
   *
   * @return string
   *   The entity property name on which contains the items.
   */
  abstract protected function itemEntityProperty();

}
