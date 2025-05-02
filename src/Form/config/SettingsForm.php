<?php

namespace Drupal\module_template\Form\config;

/**
 * @file
 * SettingsForm.php
 */

use Drupal\Core\Config\Config;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Drupal\module_template\lib\general\MarkdownParser;

/**
 * Formulario de configuración del módulo.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constructor para añadir dependencias.
   *
   * @param \Drupal\Core\Extension\ExtensionPathResolver $pathResolver
   *   Servicio PathResolver.
   */
  public function __construct(
    protected ExtensionPathResolver $pathResolver,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.path.resolver'),
    );
  }

  /**
   * Implements getFormId().
   */
  public function getFormId() {
    return 'module_template_settings';
  }

  /**
   * Implements getEditableConfigNames().
   */
  protected function getEditableConfigNames() {
    return ['module_template.settings'];
  }

  /**
   * Implements buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* Obtengo la configuración actual */
    $config = $this->config('module_template.settings');

    /* SETTINGS FORM */
    $form['settings'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['general_settings'] = $this->getGeneralSettings($config);
    $form['general_settings']['#open'] = TRUE;

    /* *************************************************************************
     * CONTENIDO DE CHANGELOG.md, LICENSE.md y README.md
     * ************************************************************************/

    /* Datos auxiliares */
    $module_path = $this->pathResolver
      ->getPath('module', "module_template");

    /* Compruebo si existe y leo el contenido del archivo CHANGELOG.md */
    $contenido = $this->getChangeLogBuild($config, $module_path);
    if ($contenido) {
      $form['info'] = $contenido;
    }

    /* Compruebo si existe y leo el contenido del archivo LICENSE.md */
    $contenido = $this->getLicenseBuild($config, $module_path);
    if ($contenido) {
      $form['license'] = $contenido;
    }

    /* Compruebo si existe y leo el contenido del archivo README.md */
    $contenido = $this->getReadmeBuild($config, $module_path);
    if ($contenido) {
      $form['help'] = $contenido;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('module_template.settings');

    /* TODO: Indicar todos los campos a guardar */
    $list = [];

    foreach ($list as $item) {
      $config->set($item, $form_state->getValue($item));
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Genera el formulario para la configuración general del módulo.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getGeneralSettings(Config $config): array {
    $form['general_settings'] = [
      '#type'        => 'details',
      '#title'       => $this->t('General'),
      '#group'       => 'settings',
      '#description' => $this->t('<p><h2>General Settings</h2></p>'),
    ];

    return $form['general_settings'];
  }

  /**
   * Obtiene el contenido del archivo CHANGELOG.md.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   * @param string $module_path
   *   Path del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getChangeLogBuild(
    Config $config,
    string $module_path,
  ): array {
    $template = file_get_contents($module_path . "/templates/custom/info.html.twig");
    $has_content = FALSE;

    $ruta = $module_path . "/CHANGELOG.md";
    $contenido = $this->getMdContent($ruta);

    $form['info'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Info'),
      '#group'       => 'settings',
      '#description' => '',
    ];

    if ($contenido) {
      $form['info']['info'] = [
        '#type'     => 'inline_template',
        '#template' => $template,
        '#context'  => [
          'changelog' => Markup::create($contenido),
        ],
      ];
      $has_content = TRUE;
    }

    $rows = $this->generateGitTable();
    if (count($rows) > 0) {
      $form['info']['git_resume'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('<h2>Git (last 10 commits)</h2>'),

        'table' => [
          '#type' => 'table',
          '#title' => $this->t('Git Resume'),
          '#header' => [
            'HASH',
            $this->t("Author"),
            $this->t("Date"),
            $this->t("Message"),
          ],
          '#rows' => $rows,
          '#empty' => $this->t('No data has been found.'),
          '#responsive' => TRUE,
          '#sticky' => FALSE,
        ],
      ];
      $has_content = TRUE;
    }

    return $has_content ? $form['info'] : [];
  }

  /**
   * Obtiene el contenido del archivo LICENSE.md.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   * @param string $module_path
   *   Path del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getLicenseBuild(
    Config $config,
    string $module_path,
  ): array {
    $template = file_get_contents($module_path . "/templates/custom/license.html.twig");

    $ruta = $module_path . "/LICENSE.md";
    $contenido = $this->getMdContent($ruta);

    if ($contenido) {
      $form['license'] = [
        '#type'        => 'details',
        '#title'       => $this->t('License'),
        '#group'       => 'settings',
        '#description' => '',

        'license' => [
          '#type'     => 'inline_template',
          '#template' => $template,
          '#context'  => [
            'license' => Markup::create($contenido),
          ],
        ],
      ];

      return $form['license'];
    }

    return [];
  }

  /**
   * Obtiene el contenido del archivo README.md.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuración del módulo.
   * @param string $module_path
   *   Path del módulo.
   *
   * @return array
   *   Array con el contenido a renderizar, si procede.
   */
  private function getReadmeBuild(
    Config $config,
    string $module_path,
  ): array {
    $template = file_get_contents($module_path . "/templates/custom/help.html.twig");

    $ruta = $module_path . "/README.md";
    $contenido = $this->getMdContent($ruta);

    if ($contenido) {
      $form['help'] = [
        '#type'        => 'details',
        '#title'       => $this->t('Help'),
        '#group'       => 'settings',
        '#description' => '',

        'help' => [
          '#type'     => 'inline_template',
          '#template' => $template,
          '#context'  => [
            'readme' => Markup::create($contenido),
          ],
        ],
      ];

      return $form['help'];
    }

    return [];
  }

  /**
   * Obtiene el contenido de un archivo .md.
   *
   * @param string $path
   *   Ruta completa del archivo.
   *
   * @return string
   *   Contenido del archivo.
   */
  private function getMdContent(string $path): string {
    $parser = new MarkdownParser();

    $contenido = '';
    if (file_exists($path)) {
      $contenido = file_get_contents($path);
      $contenido = $parser->text($contenido);
    }

    return $contenido;
  }

  /**
   * Genera un array con los últimos 10 commits de git.
   *
   * @return array
   *   Array con los datos de la tabla.
   */
  private function generateGitTable(): array {
    $row = [];
    // phpcs:ignore
    $command = ['git', 'log', '-n', '10', '--pretty=format:"%h%x09%an%x09%ad%x09%s"'];

    // Crea el proceso.
    $process = new Process($command);

    try {
      // Ejecuta el proceso
      $process->mustRun();

      // Obtiene la salida del comando
      $output = $process->getOutput();

      // Divide la salida en líneas
      $lines = explode("\n", $output);

      // Procesa cada línea
      foreach ($lines as $line) {
        // Divide la línea en sus componentes
        $row[] = explode("\t", str_replace('"', '', $line));
      }
    }
    catch (ProcessFailedException $exception) {
      // El comando falló, maneja el error.
      $this->logger('module_template')->error($exception->getMessage());
    }

    return $row;
  }

}
