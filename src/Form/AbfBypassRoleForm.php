<?php

namespace Drupal\access_by_field\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbfBypassRoleForm.
 *
 * The config form for bypassing access permission by roles.
 * @package Drupal\access_by_field\Form
 */
class AbfBypassRoleForm extends ConfigFormBase {
  /**
   * The role storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $roleStorage;

  /**
   * AbfBypassRoleForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $role_storage
   *   The role storage.
   */
  public function __construct(ConfigFactoryInterface $configFactory, TypedConfigManagerInterface $typed_config_manager, EntityStorageInterface $role_storage) {
    parent::__construct($configFactory, $typed_config_manager);
    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('entity_type.manager')->getStorage('user_role'),
    );
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return [
      'access_by_field.abf_bypass_roles.settings',
    ];
  }

  /**
   * @return string
   */
  public function getFormId() {
    return 'access_by_field_bypass_roles';
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('access_by_field.abf_bypass_roles.settings');
    foreach ($this->roleStorage->loadMultiple() as $rid => $role) {
      if ($role->id() !== 'anonymous' && $role->id() !== 'administrator') {
        $roles[$role->id()] = $role->label();
      }
    }
    $data = $config->getRawData();
    $default_value = !empty($data) ? $config->get('bypassed_roles') : [];
    $form['bypassed_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Skip access for roles:'),
      '#description' => $this->t('Roles selected here will have no restriction on access.'),
      '#default_value' => $default_value,
      '#options' => $roles,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('access_by_field.abf_bypass_roles.settings')
      ->set('bypassed_roles', $form_state->getValue('bypassed_roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
