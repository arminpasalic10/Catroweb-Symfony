<?php

namespace Catrobat\AppBundle\Services\TestEnv;

use Catrobat\AppBundle\Services\TokenGenerator;

/**
 * Class ProxyTokenGenerator
 * @package Catrobat\AppBundle\Services\TestEnv
 */
class ProxyTokenGenerator extends TokenGenerator
{
  /**
   * @var TokenGenerator
   */
  private $generator;

  /**
   * ProxyTokenGenerator constructor.
   *
   * @param TokenGenerator $default_generator
   */
  public function __construct(TokenGenerator $default_generator)
  {
    parent::__construct();
    $this->generator = $default_generator;
  }

  /**
   * @return string
   */
  public function generateToken()
  {
    return $this->generator->generateToken();
  }

  /**
   * @param TokenGenerator $generator
   */
  public function setTokenGenerator(TokenGenerator $generator)
  {
    $this->generator = $generator;
  }
}
