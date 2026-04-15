<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Groop;

use Evasystem\Core\Groop\GroopModel;

class GroopService
{
    private array $arrayadd = [];

    public function getArrayadd(): array
    {
        return $this->arrayadd;
    }

    public function getAllGroops(): array
    {
        return GroopModel::getGroopsAll();
    }

    public function getGroupsByUserId(int $idUsers): array
    {
        return GroopModel::getGroupsByUserId($idUsers);
    }

    public function getIdGroops($id): array
    {
        return GroopModel::getGroopsId($id);
    }

    public function getGroupByRandomId(string $id): array
    {
        return GroopModel::getGroupByRandomId($id);
    }

    public function getMembersByConnectId(string $connectId): array
    {
        return GroopModel::getMembersByConnectId($connectId);
    }

    public function getPostsByConnectId(string $connectId): array
    {
        return GroopModel::getPostsByConnectId($connectId);
    }

    public function getRepliesByPostId(string $postId): array
    {
        return GroopModel::getRepliesByPostId($postId);
    }

    public function getMembership(string $groupId, int $idUsers): array
    {
        return GroopModel::getMembership($groupId, $idUsers);
    }

    public function getInviteByToken(string $token): array
    {
        return GroopModel::getInviteByToken($token);
    }

    public function getInvitesByConnectId(string $connectId): array
    {
        return GroopModel::getInvitesByConnectId($connectId);
    }

    public function getPendingInviteByEmail(string $groupId, string $email): array
    {
        return GroopModel::getPendingInviteByEmail($groupId, $email);
    }

    public function deleteGroop($id)
    {
        return GroopModel::del($id);
    }

    public function deleteGroupCascade(string $groupId): void
    {
        GroopModel::deleteGroupCascade($groupId);
    }

    public function updateTaskInfo(string $taskId, array $data)
    {
        return GroopModel::udape($taskId, $data, 'groop_groups');
    }
    public function getAttachedQuizzesByConnectId(string $connectId): array
    {
        return GroopModel::getAttachedQuizzesByConnectId($connectId);
    }

    public function attachQuizToGroup(string $groupId, int $quizId, int $idUsers)
    {
        return GroopModel::attachQuizToGroup([
            'randomn_id' => $this->makeId('gquiz'),
            'connect_id' => $groupId,
            'quiz_id'    => $quizId,
            'id_users'   => $idUsers,
            'status'     => 'active',
        ]);
    }

    public function removeQuizFromGroup(string $groupId, int $quizId)
    {
        return GroopModel::removeQuizFromGroup($groupId, $quizId);
    }
    public function setArrayadd($arrayadd = null, $additionalData = [], $exceptions = []): void
    {
        $filteredPost = array_filter(
            array_diff_key((array)$arrayadd, array_flip($exceptions)),
            function ($value, $key) {
                return $key !== '' && trim((string)$value) !== '';
            },
            ARRAY_FILTER_USE_BOTH
        );

        $this->arrayadd = array_merge($filteredPost, $additionalData);
    }

    public function makeId(string $prefix = 'grp'): string
    {
        return GroopModel::makeId($prefix);
    }

    public function createGroup(array $data): array
    {
        $groupId = $this->makeId('group');

        $ok = GroopModel::createGroup([
            'randomn_id' => $groupId,
            'id_users'   => (int)($data['id_users'] ?? 0),
            'titlu'      => trim((string)($data['titlu'] ?? 'New Group')),
            'descriere'  => trim((string)($data['descriere'] ?? '')),
            'status'     => 'active',
        ]);

        if ($ok) {
            GroopModel::createMember([
                'randomn_id' => $this->makeId('member'),
                'connect_id' => $groupId,
                'id_users'   => (int)($data['id_users'] ?? 0),
                'role'       => 'owner',
                'status'     => 'active',
            ]);
        }

        return [
            'success' => (bool)$ok,
            'group_id' => $groupId
        ];
    }

    public function sendInvite(array $data): bool
    {
        return (bool)GroopModel::createInvite([
            'randomn_id'   => $this->makeId('invite'),
            'connect_id'   => trim((string)($data['group_id'] ?? '')),
            'email'        => trim((string)($data['email'] ?? '')),
            'invite_token' => $this->makeId('token'),
            'invited_by'   => (int)($data['id_users'] ?? 0),
            'status'       => 'pending',
        ]);
    }

    public function acceptInvite(string $token, int $idUsers): array
    {
        $inviteRows = $this->getInviteByToken($token);
        if (empty($inviteRows[0])) {
            return ['success' => false, 'message' => 'Invite not found'];
        }

        $invite = $inviteRows[0];

        if (($invite['status'] ?? '') !== 'pending') {
            return ['success' => false, 'message' => 'Invite is not pending'];
        }

        $existing = $this->getMembership((string)$invite['connect_id'], $idUsers);
        if (empty($existing[0])) {
            GroopModel::createMember([
                'randomn_id' => $this->makeId('member'),
                'connect_id' => (string)$invite['connect_id'],
                'id_users'   => $idUsers,
                'role'       => 'member',
                'status'     => 'active',
            ]);
        }

        GroopModel::udape((string)$invite['randomn_id'], [
            'status' => 'accepted',
            'accepted_at' => date('Y-m-d H:i:s')
        ], 'groop_invites');

        return ['success' => true];
    }

    public function createPost(array $data): bool
    {
        return (bool)GroopModel::createPost([
            'randomn_id' => $this->makeId('post'),
            'connect_id' => trim((string)($data['group_id'] ?? '')),
            'id_users'   => (int)($data['id_users'] ?? 0),
            'titlu'      => trim((string)($data['titlu'] ?? 'User')),
            'mesaj'      => trim((string)($data['mesaj'] ?? '')),
            'likes'      => 0,
            'status'     => 'active',
        ]);
    }

    public function createReply(array $data): bool
    {
        return (bool)GroopModel::createReply([
            'randomn_id' => $this->makeId('reply'),
            'post_id'    => trim((string)($data['post_id'] ?? '')),
            'connect_id' => trim((string)($data['group_id'] ?? '')),
            'id_users'   => (int)($data['id_users'] ?? 0),
            'mesaj'      => trim((string)($data['mesaj'] ?? '')),
            'status'     => 'active',
        ]);
    }

    public function removeMember(string $memberId)
    {
        return GroopModel::del($memberId, 'groop_members', 'randomn_id');
    }

    public function likePost(string $postId)
    {
        return GroopModel::incrementLike($postId);
    }

    public function deleteInvite(string $inviteId)
    {
        return GroopModel::deleteInvite($inviteId);
    }
}