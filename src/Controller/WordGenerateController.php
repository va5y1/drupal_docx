<?php

namespace Drupal\student_catalog\Controller;

use Drupal\Core\Render\Markup;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TextAlignment;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\student_catalog\StudentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Student Catalog routes.
 */
class WordGenerateController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The PhpWord writer.
   *
   * @var \PhpOffice\PhpWord\PhpWord
   */
  protected $wordWriter;

  /**
   * The controller constructor.
   *
   * @param \PhpOffice\PhpWord\PhpWord $phpword
   *   The PhpWord writer.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(PhpWord $phpword, DateFormatterInterface $date_formatter) {
    $this->wordWriter = $phpword;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('student_catalog.word_writer'),
      $container->get('date.formatter')
    );
  }

  /**
   * Builds the word document from a student entity.
   *
   * @param \Drupal\student_catalog\StudentInterface $entity
   *   The student entity.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The Response object.
   */
  public function wordGeneration(StudentInterface $entity) {
    $response = new Response();
    // Preparing the document data.
    $first_name = $entity->get('field_first_name')->value;
    $last_name = $entity->get('field_last_name')->value;
    $current_date = $this->dateFormatter->format(time(), 'custom', 'm-d-Y H-i-s');
    $filename = "{$first_name} {$last_name}({$current_date}).docx";
    $title = $first_name . ' ' . $last_name;

    $writer = $this->wordWriter;
    $writer->addTitleStyle(1,
      [
        'size' => 14,
        'bold' => TRUE,
      ],
      ['alignment' => TextAlignment::CENTER]);

    $biography = $this->getBiography($entity);
    $image_params = $this->prepareImage($entity);

    /** @var \PhpOffice\PhpWord\Element\Section $section */
    $section = $writer->addSection();
    $section->addTitle($title);
    if ($biography || $image_params) {
      $style_cell = ['borderColor' => 'white'];
      $section->addTextBreak();
      $table = $section->addTable();
      $table->addRow();

      if ($biography) {
        $cell = $table->addCell(4500, $style_cell);
        Html::addHtml($cell, $biography);
      }
      if ($image_params) {
        $table->addCell(4500, $style_cell)
          ->addImage($image_params['uri'], $image_params['style']);
      }
    }

    $writer->save($filename, 'Word2007', TRUE);
    return $response;

  }

  /**
   * Prepares value from the biography field.
   *
   * @param \Drupal\student_catalog\StudentInterface $entity
   *   The student entity.
   *
   * @return string|null
   *   The biography field value. NULL if field is empty.
   */
  protected function getBiography(StudentInterface $entity) {
    $bio_field = $entity->get('field_bio');
    if ($bio_field->isEmpty()) {
      return NULL;
    }
    $biography = '<p>' . $this->t('Bio:') . '</p>';
    $biography .= Markup::create($bio_field->value)->__toString();
    return $biography;
  }

  /**
   * Prepares value from the photo field.
   *
   * @param \Drupal\student_catalog\StudentInterface $entity
   *   The student entity.
   *
   * @return array|null
   *   Prepared values from field_photo. NULL if field is empty.
   */
  protected function prepareImage(StudentInterface $entity) {
    $image_field = $entity->get('field_photo');
    if ($image_field->isEmpty()) {
      return NULL;
    }
    $image_styles = [
      'width'         => 250,
      'height'        => 250,
    ];
    return [
      'uri' => $image_field->entity->getFileUri(),
      'style' => $image_styles,
    ];
  }

}
