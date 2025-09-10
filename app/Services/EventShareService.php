<?php

namespace App\Services;

use App\Models\EventShare;
use App\Models\Group;
use App\Models\Member;
use App\Repositories\Interfaces\EventShareRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\MemberRepositoryInterface;
use App\Services\Interfaces\EventShareServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\EventShareInvitationMail;

class EventShareService implements EventShareServiceInterface
{
    /**
     * @var EventShareRepositoryInterface
     */
    protected $eventShareRepository;

    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    /**
     * EventShareService constructor.
     *
     * @param EventShareRepositoryInterface $eventShareRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param MemberRepositoryInterface $memberRepository
     */
    public function __construct(
        EventShareRepositoryInterface $eventShareRepository,
        GroupEventRepositoryInterface $groupEventRepository,
        GroupRepositoryInterface $groupRepository,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->eventShareRepository = $eventShareRepository;
        $this->groupEventRepository = $groupEventRepository;
        $this->groupRepository = $groupRepository;
        $this->memberRepository = $memberRepository;
    }

    /**
     * Get all shares for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventShares(int $eventId): Collection
    {
        return $this->eventShareRepository->getEventShares($eventId);
    }

    /**
     * Get a specific share by ID.
     *
     * @param int $shareId
     * @return EventShare|null
     */
    public function getShareById(int $shareId): ?EventShare
    {
        return $this->eventShareRepository->getShareById($shareId);
    }

    /**
     * Get a share by token.
     *
     * @param string $token
     * @return EventShare|null
     */
    public function getShareByToken(string $token): ?EventShare
    {
        return $this->eventShareRepository->getShareByToken($token);
    }

    /**
     * Create a new share for an event.
     *
     * @param array $data
     * @return EventShare
     */
    public function createShare(array $data): EventShare
    {
        // Generate a unique token if not provided
        if (!isset($data['token'])) {
            $data['token'] = Str::random(32);
        }

        // Set default expiration if not provided
        if (!isset($data['expires_at'])) {
            $data['expires_at'] = Carbon::now()->addDays(7);
        }

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        $share = $this->eventShareRepository->createShare($data);

        // Send invitation notification
        $this->sendShareInvitation($share);

        return $share;
    }

    /**
     * Update an existing share.
     *
     * @param int $shareId
     * @param array $data
     * @return EventShare|null
     */
    public function updateShare(int $shareId, array $data): ?EventShare
    {
        // Set timestamps based on status changes
        if (isset($data['status'])) {
            $share = $this->getShareById($shareId);
            
            if ($share) {
                if ($data['status'] === 'accepted' && $share->status !== 'accepted') {
                    $data['accepted_at'] = Carbon::now();
                } elseif ($data['status'] === 'declined' && $share->status !== 'declined') {
                    $data['declined_at'] = Carbon::now();
                }
            }
        }

        return $this->eventShareRepository->updateShare($shareId, $data);
    }

    /**
     * Delete a share.
     *
     * @param int $shareId
     * @return bool
     */
    public function deleteShare(int $shareId): bool
    {
        return $this->eventShareRepository->deleteShare($shareId);
    }

    /**
     * Get shares by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @return Collection
     */
    public function getSharesByStatus(int $eventId, string $status): Collection
    {
        return $this->eventShareRepository->getSharesByStatus($eventId, $status);
    }

    /**
     * Get shares for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getSharesForGroup(int $groupId): Collection
    {
        return $this->eventShareRepository->getSharesForGroup($groupId);
    }

    /**
     * Get shares for a member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSharesForMember(int $memberId): Collection
    {
        return $this->eventShareRepository->getSharesForMember($memberId);
    }

    /**
     * Accept a share invitation.
     *
     * @param string $token
     * @return bool
     */
    public function acceptShare(string $token): bool
    {
        $share = $this->getShareByToken($token);

        if (!$share || $share->status !== 'pending' || $share->isExpired()) {
            return false;
        }

        return $this->eventShareRepository->acceptShare($token);
    }

    /**
     * Decline a share invitation.
     *
     * @param string $token
     * @return bool
     */
    public function declineShare(string $token): bool
    {
        $share = $this->getShareByToken($token);

        if (!$share || $share->status !== 'pending' || $share->isExpired()) {
            return false;
        }

        return $this->eventShareRepository->declineShare($token);
    }

    /**
     * Send share invitation notification.
     *
     * @param EventShare $share
     * @return bool
     */
    public function sendShareInvitation(EventShare $share): bool
    {
        try {
            $event = $this->groupEventRepository->getGroupEventById($share->group_event_id);
            
            if (!$event) {
                Log::error("Failed to send share invitation: Event not found", ['share_id' => $share->id]);
                return false;
            }

            $sender = $this->memberRepository->getMemberById($share->shared_by);
            $senderName = $sender ? $sender->full_name : 'A church member';

            // Prepare invitation data
            $invitationData = [
                'event' => $event,
                'share' => $share,
                'sender' => $sender,
                'message' => $share->message ?: "{$senderName} has invited you to the event: {$event->title}",
                'token' => $share->token,
                'expires_at' => $share->expires_at
            ];

            // Send based on share type
            switch ($share->share_type) {
                case 'group':
                    return $this->sendGroupInvitation($share, $invitationData);
                case 'member':
                    return $this->sendMemberInvitation($share, $invitationData);
                case 'email':
                    return $this->sendEmailInvitation($share, $invitationData);
                default:
                    Log::error("Unknown share type", ['share_id' => $share->id, 'type' => $share->share_type]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send share invitation: {$e->getMessage()}", [
                'share_id' => $share->id,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Send invitation to a group.
     *
     * @param EventShare $share
     * @param array $invitationData
     * @return bool
     */
    protected function sendGroupInvitation(EventShare $share, array $invitationData): bool
    {
        if (!$share->shared_with_group_id) {
            return false;
        }

        $group = $this->groupRepository->getGroupById($share->shared_with_group_id);
        
        if (!$group) {
            return false;
        }

        $members = $this->groupRepository->getGroupMembers($group->id);
        $sentCount = 0;

        foreach ($members as $member) {
            if ($member->email) {
                Mail::to($member->email)->queue(new EventShareInvitationMail($invitationData));
                Log::info("Queued group event share invitation to {$member->email}", [
                    'event_id' => $invitationData['event']->id,
                    'group_id' => $group->id,
                    'member_id' => $member->id
                ]);
                $sentCount++;
            }
        }

        return $sentCount > 0;
    }

    /**
     * Send invitation to a member.
     *
     * @param EventShare $share
     * @param array $invitationData
     * @return bool
     */
    protected function sendMemberInvitation(EventShare $share, array $invitationData): bool
    {
        if (!$share->shared_with_member_id) {
            return false;
        }

        $member = $this->memberRepository->getMemberById($share->shared_with_member_id);
        
        if (!$member || !$member->email) {
            return false;
        }

        Mail::to($member->email)->queue(new EventShareInvitationMail($invitationData));
        Log::info("Queued member event share invitation to {$member->email}", [
            'event_id' => $invitationData['event']->id,
            'member_id' => $member->id
        ]);

        return true;
    }

    /**
     * Send invitation to an email address.
     *
     * @param EventShare $share
     * @param array $invitationData
     * @return bool
     */
    protected function sendEmailInvitation(EventShare $share, array $invitationData): bool
    {
        if (!$share->shared_with_email) {
            return false;
        }

        Mail::to($share->shared_with_email)->queue(new EventShareInvitationMail($invitationData));
        Log::info("Queued email event share invitation to {$share->shared_with_email}", [
            'event_id' => $invitationData['event']->id
        ]);

        return true;
    }
}
