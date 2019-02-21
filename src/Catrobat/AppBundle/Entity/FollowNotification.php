<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FollowNotification extends CatroNotification
{
  /**
   * @ORM\ManyToOne(targetEntity="\Catrobat\AppBundle\Entity\User")
   * @ORM\JoinColumn(name="follower_id", referencedColumnName="id", nullable=true)
   */
  private $follower;

  /**
   *  You have to set this parameter otherwise the wrong template will be
   *       rendered.
   */
  private $twig_template = "/Notifications/NotificationTypes/follow_notification.html.twig";

  /**
   * CommentNotification constructor.
   *
   * @param User $user
   * @param      $title
   * @param      $message
   * @param      $profile
   *
   */
  public function __construct(User $user, $title, $message, $profile)
  {
    parent::__construct($user, $title, $message);
    $this->follower = $profile;
  }

  /**
   * @return User
   */
  public function getFollower()
  {
    return $this->follower;
  }

  /**
   * @param $follower
   */
  public function setFollower($follower)
  {
    $this->follower = $follower;
  }

  /**
   * its important to overwrite the get method, otherwise it won't work
   * and the wrong template will be rendered
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }


}