<?php

namespace Drupal\condition_rulesets;

trait SharedTempstoreTrait {

  /**
   * Shared tempstore.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $sharedTempstore;

  /**
   * Shared tempstore key.
   *
   * @var string
   */
  protected $sharedTempstoreKey;

  /**
   * Set the shared tempstore.
   *
   * @param string $tempstore_id
   *   The shared tempstore identifier.
   * @param string $key
   *   The shared tempstore key.
   */
  public function setSharedTempstore($tempstore_id, $key) {
    $this->sharedTempstore = $this->sharedTempstore()->get($tempstore_id);
    $this->sharedTempstoreKey = $key;
  }

  /**
   * Get the tempstore values.
   *
   * @return array
   *   An array of the tempstore values.
   */
  protected function getTempstoreValues() {
    return $this->sharedTempstore->get($this->sharedTempstoreKey);
  }

  /**
   * Set the tempstore values.
   */
  protected function setTempstoreValues(array $values) {
    $this->sharedTempstore->set($this->sharedTempstoreKey, $values);
  }

  /**
   * Get the shared tempstore object.
   *
   * @return \Drupal\user\SharedTempStoreFactory
   *   The shared tempstore factory object.
   */
  protected function sharedTempstore() {
    return \Drupal::service('user.shared_tempstore');
  }

}
