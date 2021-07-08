<?php

namespace Drupal\student_catalog;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the student entity type.
 */
class StudentListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new StudentListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination, RendererInterface $renderer) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('redirect.destination'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total students: @total', ['@total' => $total]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['first_name'] = $this->t('First Name');
    $header['last_name'] = $this->t('Last Name');
    $header['photo'] = $this->t('Photo');
    $header['bio'] = $this->t('Biography');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Updated');
    $header['docx'] = $this->t('DOCX');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\student_catalog\StudentInterface */
    $row['id'] = $entity->toLink();
    $row['first_name'] = $entity->get('field_first_name')->value;
    $row['last_name'] = $entity->get('field_last_name')->value;
    $image = $entity->get('field_photo');
    if (!$image->isEmpty()) {
      $image_uri = $image->entity->getFileUri();
      $image_array = [
        '#theme' => 'image_style',
        '#style_name' => 'medium',
        '#uri' => $image_uri,
      ];
      $row['photo'] = $this->renderer->render($image_array);
    }
    else {
      $row['photo'] = NULL;
    }
    $bio = $entity->get('field_bio')->value;
    $row['bio'] = Markup::create($bio);
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime());
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime());
    $url = Url::fromRoute('student_catalog.document_page', ['student' => $entity->id()]);
    $link = Link::fromTextAndUrl($this->t('DOCX Version'), $url);
    $row['docx'] = $link;

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $destination = $this->redirectDestination->getAsArray();
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    return $operations;
  }

}
