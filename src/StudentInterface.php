<?php

namespace Drupal\student_catalog;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a student entity type.
 */
interface StudentInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the student creation timestamp.
   *
   * @return int
   *   Creation timestamp of the student.
   */
  public function getCreatedTime();

  /**
   * Sets the student creation timestamp.
   *
   * @param int $timestamp
   *   The student creation timestamp.
   *
   * @return \Drupal\student_catalog\StudentInterface
   *   The called student entity.
   */
  public function setCreatedTime($timestamp);

}
