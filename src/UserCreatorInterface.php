<?php

namespace Drupal\user_api;

/**
 * Interface UserCreatorInterface.
 */
interface UserCreatorInterface {
  public function createUser($name, $email);
}
