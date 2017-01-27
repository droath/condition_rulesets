<?php

namespace Drupal\condition_rulesets\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class \Drupal\condition_rulesets\Controller\ConditionRulesetsListBuilder.
 */
class ConditionRulesetsListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);

    if (!empty($operations['edit'])) {
      $edit = $operations['edit']['url'];
      $edit->setRouteParameters([
        'machine_name' => $entity->id(),
        'step' => 'general',
      ]);
    }

    return $operations;
  }

}
