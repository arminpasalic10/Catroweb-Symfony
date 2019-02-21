<?php

namespace Catrobat\AppBundle\Admin;


use Sonata\Form\Validator\ErrorElement;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;


/**
 * Class UserAdmin
 * @package Catrobat\AppBundle\Admin
 */
class UserAdmin extends BaseUserAdmin
{

  /**
   * @return \Symfony\Component\Form\FormBuilder|\Symfony\Component\Form\FormBuilderInterface
   *
   * Override FormBuilder to disable default validation
   */
  public function getFormBuilder()
  {
    $this->formOptions['data_class'] = $this->getClass();

    $options = $this->formOptions;

    $options['validation_groups'] = [];

    $formBuilder = $this->getFormContractor()->getFormBuilder($this->getUniqid(), $options);

    $this->defineFormBuilder($formBuilder);

    return $formBuilder;
  }


  /**
   * @param ErrorElement $errorElement
   * @param              $object
   *
   * rewrite validation
   */
  public function validate(ErrorElement $errorElement, $object)
  {
    $errorElement
      ->with('username')
      ->addConstraint(new \Symfony\Component\Validator\Constraints\NotBlank())
      ->addConstraint(new \Symfony\Component\Validator\Constraints\Regex(['pattern' => "/^[\w@_\-\.]+$/"]))
      ->end()
      ->with('email')
      ->addConstraint(new \Symfony\Component\Validator\Constraints\NotBlank())
      ->addConstraint(new \Symfony\Component\Validator\Constraints\Email())
      ->end();
  }
}