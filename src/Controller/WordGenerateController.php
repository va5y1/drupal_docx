<?php

namespace Drupal\student_catalog\Controller;

use Drupal\Core\Render\Markup;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TextAlignment;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\student_catalog\StudentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Student Catalog routes.
 */
class WordGenerateController extends ControllerBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(RendererInterface $renderer, DateFormatterInterface $date_formatter) {
    $this->renderer = $renderer;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('date.formatter')
    );
  }

  /**
   * Builds the response.
   *
   * @param \Drupal\student_catalog\StudentInterface $entity
   *
   * @return mixed
   *
   * @throws \PhpOffice\PhpWord\Exception\Exception
   */
  public function wordGeneration(StudentInterface $entity) {
    // Creating the new document...
    $first_name = $entity->get('field_first_name')->value;
    $last_name = $entity->get('field_last_name')->value;
    $current_date = $this->dateFormatter->format(time(), 'custom', 'm-d-Y H-i-s');
    $filename = "{$first_name} {$last_name}({$current_date}).docx";
    $title = $first_name . ' ' . $last_name;
    $bio = $entity->get('field_bio');
    $biorobot = '';

    $image_uri = '';
    $image = $entity->get('field_photo');


    /* Note: any element you append to a document must reside inside of a Section. */

    // Memory cleanup.
    //    $phpWord->disconnectWorksheets();
    //    unset($spreadsheet);
    $response = new Response();
    // $response->headers->set('Pragma', 'no-cache');
    //    $response->headers->set('Expires', '0');
    //    $response->headers->set('Content-Type', 'application/octet-stream');
    //    $response->headers->set('Content-Disposition', "attachment; filename=\"{$first_name} {$last_name} - {$current_date}.docx\"");

    /** @var \PhpOffice\PhpWord\PhpWord $writer */
    $writer = \Drupal::service('student_catalog.word_writer');
    $writer->addTitleStyle(1,
      [
        'size' => 14,
        'bold' => TRUE,
      ],
      ['alignment' => TextAlignment::CENTER]);
    // $section2 = $writer->addSection();
    //    $section2->addTitle($title);
    //    $writer->addTitle($section2);
    $settings = $writer->getSettings();

    /** @var \PhpOffice\PhpWord\Element\Section $section */
    $section = $writer->addSection();
    $section_style = $section->getStyle();

    $tableStyle = ['borderSize' => 1, 'borderColor' => '999999', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0];
    $styleCell = ['borderTopSize' => 1 , 'borderTopColor' => 'black', 'borderLeftSize' => 1, 'borderLeftColor' => 'black', 'borderRightSize' => 1, 'borderRightColor' => 'black', 'borderBottomSize' => 1, 'borderBottomColor' => 'black'];
    $fontStyle = ['italic' => TRUE, 'size' => 11, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0];
    // $section_style->setAuto();
    // $section->setStyle($section_style);
    // Simple text.
    $section->addTitle($title);
    $section->addTextBreak();
    $table = $section->addTable($fontStyle);
    $table->addRow();
    if (!$bio->isEmpty()) {
      $biorobot .= '<p>' . t('Bio:') . '</p>';
      $biorobot .= Markup::create($bio->value)->__toString();
      $cell = $table->addCell(4500);
      Html::addHtml($cell, $biorobot);
    }

    if (!$image->isEmpty()) {
      $image_uri = $image->entity->getFileUri();
      $table->addCell(4500)->addImage(
        $image_uri,
        [
          'width'         => 250,
          'height'        => 250,
          'marginTop'     => -1,
          'marginLeft'    => -1,
          'wrappingStyle' => 'behind',
        ]
      );
    }


    $writer->save($filename, 'Word2007', TRUE);
    return $response;

  }

}
