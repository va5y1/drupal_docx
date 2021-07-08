<?php

namespace Drupal\student_catalog\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\student_catalog\StudentInterface;

/**
 * Defines the student entity class.
 *
 * @ContentEntityType(
 *   id = "student",
 *   label = @Translation("Student"),
 *   label_collection = @Translation("Students"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\student_catalog\StudentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\student_catalog\StudentAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\student_catalog\Form\StudentForm",
 *       "edit" = "Drupal\student_catalog\Form\StudentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "student",
 *   admin_permission = "administer student",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/students/add",
 *     "canonical" = "/student/{student}",
 *     "edit-form" = "/student/{student}/edit",
 *     "delete-form" = "/student/{student}/delete",
 *     "collection" = "/admin/structure/students"
 *   },
 *   field_ui_base_route = "entity.student.settings"
 * )
 */
class Student extends ContentEntityBase implements StudentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the student was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the student was last edited.'));

    $fields['field_first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('The first name of the student.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['field_last_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last Name'))
      ->setDescription(t('The last name of the student.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['field_photo'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Photo'))
      ->setDescription(t("The student photo."))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_bio'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Bio'))
      ->setDescription(t('The student biography.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
