<?php

namespace App\Api;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\AnniversaryNotification;
use App\Entity\CatroNotification;
use App\Entity\CommentNotification;
use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\NewProgramNotification;
use App\Entity\RemixManager;
use App\Entity\RemixNotification;
use App\Entity\User;
use App\Repository\CatroNotificationRepository;
use App\Utils\APIHelper;
use Exception;
use OpenAPI\Server\Api\NotificationsApiInterface;
use OpenAPI\Server\Model\NotificationsCountResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationsApi extends AbstractController implements NotificationsApiInterface
{
  private CatroNotificationRepository $notification_repo;
  private RemixManager $remix_manager;
  private CatroNotificationService $notification_service;
  private TranslatorInterface $translator;

  public function __construct(CatroNotificationRepository $notification_repo, RemixManager $remix_manager,
                              CatroNotificationService $notification_service, TranslatorInterface $translator)
  {
    $this->notification_repo = $notification_repo;
    $this->remix_manager = $remix_manager;
    $this->notification_service = $notification_service;
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function setPandaAuth($value): void
  {
    $this->token = APIHelper::getPandaAuth($value);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function notificationsCountGet(&$responseCode, array &$responseHeaders)
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
    }

    $catro_user_notifications_all = $this->notification_repo->findBy(['user' => $user]);
    $likes = 0;
    $followers = 0;
    $comments = 0;
    $remixes = 0;
    $all = 0;
    foreach ($catro_user_notifications_all as $notification)
    {
      /** @var CatroNotification $notification */
      if ($notification->getSeen())
      {
        continue;
      }

      if ($notification instanceof LikeNotification)
      {
        ++$likes;
      }
      elseif ($notification instanceof FollowNotification || $notification instanceof NewProgramNotification)
      {
        ++$followers;
      }
      elseif ($notification instanceof CommentNotification)
      {
        ++$comments;
      }
      elseif ($notification instanceof RemixNotification)
      {
        ++$remixes;
      }

      ++$all;
    }

    $unseen_remixed_program_data = $this->remix_manager->getUnseenRemixProgramsDataOfUser($user);

    return $this->countResponse($all, $likes, $followers, $comments, $remixes);
  }

  public function notificationsGet(int $limit = 20, int $offset = 0, string $type = null, &$responseCode, array &$responseHeaders)
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
    }
    $catro_user_notifications = null;
    if ('all' === $type)
    {
      $catro_user_notifications = $this->notification_repo->findBy(['user' => $user], ['id' => 'DESC'], $limit, $offset);
    }
    else
    {
      $catro_user_notifications = $this->notification_repo->findBy(['user' => $user, 'type' => $type], ['id' => 'DESC'], $limit, $offset);
    }

    $fetched_notifications = [];
    foreach ($catro_user_notifications as $notification)
    {
      if ($notification instanceof LikeNotification && ('reaction' === $type || 'all' === $type))
      {
        if (($notification->getLikeFrom() === $this->getUser()))
        {
          continue;
        }
        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => $notification->getLikeFrom()->getId(),
            'from_name' => $notification->getLikeFrom()->getUsername(),
            'program' => $notification->getProgram()->getId(),
            'program_name' => $notification->getProgram()->getName(),
            'avatar' => $notification->getLikeFrom()->getAvatar(),
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'reaction',
            'message' => $this->translator->trans('catro-notifications.like.message', [], 'catroweb'),
            'prize' => null,
            'seen' => $notification->getSeen(), ]);

        continue;
      }
      if (($notification instanceof FollowNotification || $notification instanceof NewProgramNotification)
        && ('follow' === $type || 'all' === $type))
      {
        if (($notification instanceof FollowNotification && $notification->getFollower() === $this->getUser())
          || ($notification instanceof NewProgramNotification && $notification->getProgram()->getUser() === $this->getUser()))
        {
          continue;
        }
        if ($notification instanceof FollowNotification)
        {
          array_push($fetched_notifications,
            ['id' => $notification->getId(),
              'from' => $notification->getFollower()->getId(),
              'from_name' => $notification->getFollower()->getUsername(),
              'program' => null,
              'program_name' => null,
              'avatar' => $notification->getFollower()->getAvatar(),
              'remixed_program' => null,
              'remixed_program_name' => null,
              'type' => 'follow',
              'message' => $this->translator->trans('catro-notifications.follow.message', [], 'catroweb'),
              'prize' => null,
              'seen' => $notification->getSeen(), ]);
        }
        else
        {
          array_push($fetched_notifications,
            ['id' => $notification->getId(),
              'from' => $notification->getProgram()->getUser()->getId(),
              'from_name' => $notification->getProgram()->getUser()->getUsername(),
              'program' => $notification->getProgram()->getId(),
              'program_name' => $notification->getProgram()->getName(),
              'avatar' => $notification->getProgram()->getUser()->getAvatar(),
              'remixed_program' => null,
              'remixed_program_name' => null,
              'type' => 'program',
              'message' => $this->translator->trans('catro-notifications.program-upload.message', [], 'catroweb'),
              'prize' => null,
              'seen' => $notification->getSeen(), ]);
        }
        continue;
      }
      if ($notification instanceof CommentNotification && ('comment' === $type || 'all' === $type))
      {
        if ($notification->getComment()->getUser() === $this->getUser())
        {
          continue;
        }
        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => $notification->getComment()->getUser()->getId(),
            'from_name' => $notification->getComment()->getUser()->getUsername(),
            'program' => $notification->getComment()->getProgram()->getId(),
            'program_name' => $notification->getComment()->getProgram()->getName(),
            'avatar' => $notification->getComment()->getUser()->getAvatar(),
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'comment',
            'message' => $this->translator->trans('catro-notifications.comment.message', [], 'catroweb'),
            'prize' => null,
            'seen' => $notification->getSeen(), ]);
        continue;
      }
      if ($notification instanceof RemixNotification && ('remix' === $type || 'all' === $type))
      {
        if ($notification->getRemixFrom() === $this->getUser())
        {
          continue;
        }

        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => $notification->getRemixFrom()->getId(),
            'from_name' => $notification->getRemixFrom()->getUsername(),
            'program' => $notification->getRemixProgram()->getId(),
            'program_name' => $notification->getRemixProgram()->getName(),
            'avatar' => $notification->getRemixFrom()->getAvatar(),
            'remixed_program' => $notification->getProgram()->getId(),
            'remixed_program_name' => $notification->getProgram()->getName(),
            'type' => 'remix',
            'message' => $this->translator->trans('catro-notifications.remix.message', [], 'catroweb'),
            'prize' => null,
            'seen' => $notification->getSeen(), ]);
        continue;
      }
      if ('all' === $type)
      {
        $prize = null;
        if ($notification instanceof AnniversaryNotification)
        {
          $prize = $notification->getPrize();
        }
        array_push($fetched_notifications,
          ['id' => $notification->getId(),
            'from' => null,
            'from_name' => null,
            'program' => 'other',
            'program_name' => null,
            'avatar' => null,
            'remixed_program' => null,
            'remixed_program_name' => null,
            'type' => 'other',
            'message' => $notification->getMessage(),
            'prize' => $prize,
            'seen' => $notification->getSeen(), ]);
      }
    }

    return $this->fetchResponses($fetched_notifications);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function notificationsMarkallPut(&$responseCode, array &$responseHeaders)
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
    }
    $catro_user_notifications = $this->notification_repo->findBy(['user' => $user]);
    $notifications_seen = [];
    foreach ($catro_user_notifications as $notification)
    {
      if (!$notification)
      {
        $responseCode = Response::HTTP_NOT_FOUND;
      }
      if (!$notification->getSeen())
      {
        $notifications_seen[$notification->getID()] = $notification;
      }
    }
    $this->notification_service->markSeen($notifications_seen);
    $this->remix_manager->markAllUnseenRemixRelationsOfUserAsSeen($user);
    $responseCode = Response::HTTP_NO_CONTENT;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function notificationsMarkasreadIdPut(int $id, &$responseCode, array &$responseHeaders)
  {
    $user = $this->getUser();
    if (null === $user)
    {
      $responseCode = Response::HTTP_UNAUTHORIZED;
    }
    $notification_seen = $this->notification_repo->findOneBy(['id' => $id, 'user' => $user]);
    if (null === $notification_seen)
    {
      $responseCode = Response::HTTP_NOT_FOUND;
    }
    $this->notification_service->markSeen([$notification_seen]);

    $responseCode = Response::HTTP_NO_CONTENT;
  }

  private function countResponse($all_count, $likes_count, $followers_count, $comments_count, $remix_count): NotificationsCountResponse
  {
    return new NotificationsCountResponse([
      'all' => $all_count,
      'likes' => $likes_count,
      'followers' => $followers_count,
      'comments' => $comments_count,
      'remixes' => $remix_count,
    ]);
  }

  private function content($from, $from_name, $program, $program_name, $avatar, $remixed_program,
  $remixed_program_name, $message, $prize): NotificationContent
  {
    return new NotificationContent([
      'from' => $from,
      'from_name' => $from_name,
      'program' => $program,
      'program_name' => $program_name,
      'avatar' => $avatar,
      'remixed_program' => $remixed_program,
      'remixed_program_name' => $remixed_program_name,
      'message' => $message,
      'prize' => $prize,
    ]);
  }

  private function fetchResponse($notification): NotificationsFetchResponse
  {
    $notification_content = $this->content($notification['from'], $notification['from_name'],
    $notification['program'], $notification['program_name'], $notification['avatar'],
    $notification['remixed_program'], $notification['remixed_program_name'], $notification['message'],
    $notification['prize']);

    return new NotificationsFetchResponse([
      'id' => $notification['id'],
      'type' => $notification['type'],
      'seen' => $notification['seen'],
      'content' => $notification_content,
    ]);
  }

  private function fetchResponses(array $fetched_notifications): array
  {
    $fetchResponses = [];
    foreach ($fetched_notifications as $notification)
    {
      $notificationData = $this->fetchResponse($notification);
      $fetchResponses[] = $notificationData;
    }

    return $fetchResponses;
  }
}
