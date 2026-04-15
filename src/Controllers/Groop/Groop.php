<?php

declare(strict_types=1);

namespace Evasystem\Controllers\Groop;

class Groop
{
    private GroopService $groopService;

    public function __construct(GroopService $groopService)
    {
        $this->groopService = $groopService;
    }

    public function createGroup(array $data): array
    {
        try {
            $result = $this->groopService->createGroup($data);
            $groups = $this->groopService->getGroupsByUserId((int)($data['id_users'] ?? 0));

            return [
                'success' => (bool)$result['success'],
                'message' => $result['success'] ? 'Group created.' : 'Group not created.',
                'group' => ['randomn_id' => $result['group_id'] ?? ''],
                'groups' => $groups
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Create group error: ' . $e->getMessage()];
        }
    }
    public function attachQuiz(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $quizId = (int)($data['quiz_id'] ?? 0);
            $idUsers = (int)($data['id_users'] ?? 0);

            if ($groupId === '' || $quizId <= 0) {
                return ['success' => false, 'message' => 'Date incomplete'];
            }

            $membership = $this->groopService->getMembership($groupId, $idUsers);
            if (empty($membership[0])) {
                return ['success' => false, 'message' => 'Nu ai acces în acest grup'];
            }

            $this->groopService->attachQuizToGroup($groupId, $quizId, $idUsers);
            $attached = $this->groopService->getAttachedQuizzesByConnectId($groupId);

            return [
                'success' => true,
                'message' => 'Quiz attached.',
                'attached_quizzes' => $attached
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Attach quiz error: ' . $e->getMessage()];
        }
    }

    public function removeAttachedQuiz(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $quizId = (int)($data['quiz_id'] ?? 0);
            $idUsers = (int)($data['id_users'] ?? 0);

            if ($groupId === '' || $quizId <= 0) {
                return ['success' => false, 'message' => 'Date incomplete'];
            }

            $membership = $this->groopService->getMembership($groupId, $idUsers);
            if (empty($membership[0])) {
                return ['success' => false, 'message' => 'Nu ai acces în acest grup'];
            }

            $this->groopService->removeQuizFromGroup($groupId, $quizId);
            $attached = $this->groopService->getAttachedQuizzesByConnectId($groupId);

            return [
                'success' => true,
                'message' => 'Quiz removed.',
                'attached_quizzes' => $attached
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Remove attached quiz error: ' . $e->getMessage()];
        }
    }
    public function deleteGroup(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $groupRows = $this->groopService->getGroupByRandomId($groupId);

            if (empty($groupRows[0])) {
                return ['success' => false, 'message' => 'Group not found'];
            }

            $group = $groupRows[0];
            if ((int)($group['id_users'] ?? 0) !== (int)($data['id_users'] ?? 0)) {
                return ['success' => false, 'message' => 'Nu ai dreptul să ștergi acest grup'];
            }

            $this->groopService->deleteGroupCascade($groupId);

            return [
                'success' => true,
                'message' => 'Group deleted.'
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Delete group error: ' . $e->getMessage()];
        }
    }

    public function loadGroup(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $membership = $this->groopService->getMembership($groupId, $idUsers);
            if (empty($membership[0])) {
                return ['success' => false, 'message' => 'Nu ai acces în acest grup'];
            }

            $group = $this->groopService->getGroupByRandomId($groupId);
            $members = $this->groopService->getMembersByConnectId($groupId);
            $posts = $this->groopService->getPostsByConnectId($groupId);
            $invites = $this->groopService->getInvitesByConnectId($groupId);

            foreach ($posts as &$post) {
                $post['replies'] = $this->groopService->getRepliesByPostId((string)$post['randomn_id']);
            }
            unset($post);

            return [
                'success' => true,
                'group' => $group[0] ?? null,
                'members' => $members,
                'posts' => $posts,
                'invites' => $invites,
                'invite_link' => '/join_group.php?group=' . urlencode($groupId)
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Load group error: ' . $e->getMessage()];
        }
    }

    public function sendInvite(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $email = trim((string)($data['email'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $group = $this->groopService->getGroupByRandomId($groupId);
            if (empty($group[0])) {
                return ['success' => false, 'message' => 'Group not found'];
            }

            if ((int)($group[0]['id_users'] ?? 0) !== $idUsers) {
                return ['success' => false, 'message' => 'Doar ownerul poate invita'];
            }

            $existingInvite = $this->groopService->getPendingInviteByEmail($groupId, $email);
            if (!empty($existingInvite[0])) {
                return ['success' => false, 'message' => 'Există deja invitație pending pentru acest email'];
            }

            $ok = $this->groopService->sendInvite($data);
            $invites = $this->groopService->getInvitesByConnectId($groupId);

            return [
                'success' => $ok,
                'message' => $ok ? 'Invite created.' : 'Invite failed.',
                'invites' => $invites
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Send invite error: ' . $e->getMessage()];
        }
    }

    public function acceptInvite(array $data): array
    {
        try {
            $token = trim((string)($data['invite_token'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            return $this->groopService->acceptInvite($token, $idUsers);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Accept invite error: ' . $e->getMessage()];
        }
    }

    public function createPost(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $membership = $this->groopService->getMembership($groupId, $idUsers);
            if (empty($membership[0])) {
                return ['success' => false, 'message' => 'Nu poți scrie în acest grup'];
            }

            $ok = $this->groopService->createPost($data);
            $posts = $this->groopService->getPostsByConnectId($groupId);

            foreach ($posts as &$post) {
                $post['replies'] = $this->groopService->getRepliesByPostId((string)$post['randomn_id']);
            }
            unset($post);

            return [
                'success' => $ok,
                'message' => $ok ? 'Post created.' : 'Post failed.',
                'posts' => $posts
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Create post error: ' . $e->getMessage()];
        }
    }

    public function createReply(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $membership = $this->groopService->getMembership($groupId, $idUsers);
            if (empty($membership[0])) {
                return ['success' => false, 'message' => 'Nu poți răspunde în acest grup'];
            }

            $ok = $this->groopService->createReply($data);
            $posts = $this->groopService->getPostsByConnectId($groupId);

            foreach ($posts as &$post) {
                $post['replies'] = $this->groopService->getRepliesByPostId((string)$post['randomn_id']);
            }
            unset($post);

            return [
                'success' => $ok,
                'message' => $ok ? 'Reply created.' : 'Reply failed.',
                'posts' => $posts
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Create reply error: ' . $e->getMessage()];
        }
    }

    public function removeMember(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $memberId = trim((string)($data['member_id'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $group = $this->groopService->getGroupByRandomId($groupId);
            if (empty($group[0]) || (int)($group[0]['id_users'] ?? 0) !== $idUsers) {
                return ['success' => false, 'message' => 'Doar ownerul poate elimina membri'];
            }

            $this->groopService->removeMember($memberId);
            $members = $this->groopService->getMembersByConnectId($groupId);

            return [
                'success' => true,
                'message' => 'Member removed.',
                'members' => $members
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Remove member error: ' . $e->getMessage()];
        }
    }

    public function likePost(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $membership = $this->groopService->getMembership($groupId, $idUsers);
            if (empty($membership[0])) {
                return ['success' => false, 'message' => 'Nu ai acces în acest grup'];
            }

            $this->groopService->likePost((string)($data['post_id'] ?? ''));
            $posts = $this->groopService->getPostsByConnectId($groupId);

            foreach ($posts as &$post) {
                $post['replies'] = $this->groopService->getRepliesByPostId((string)$post['randomn_id']);
            }
            unset($post);

            return [
                'success' => true,
                'message' => 'Post liked.',
                'posts' => $posts
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Like error: ' . $e->getMessage()];
        }
    }

    public function deleteInvite(array $data): array
    {
        try {
            $groupId = trim((string)($data['group_id'] ?? ''));
            $inviteId = trim((string)($data['invite_id'] ?? ''));
            $idUsers = (int)($data['id_users'] ?? 0);

            $group = $this->groopService->getGroupByRandomId($groupId);
            if (empty($group[0]) || (int)($group[0]['id_users'] ?? 0) !== $idUsers) {
                return ['success' => false, 'message' => 'Doar ownerul poate șterge invitații'];
            }

            $this->groopService->deleteInvite($inviteId);
            $invites = $this->groopService->getInvitesByConnectId($groupId);

            return [
                'success' => true,
                'message' => 'Invite deleted.',
                'invites' => $invites
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Delete invite error: ' . $e->getMessage()];
        }
    }
}