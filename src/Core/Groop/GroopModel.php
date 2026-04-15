<?php

namespace Evasystem\Core\Groop;

use Evasystem\Core\AdvancedCRUD;

class GroopModel
{
    public static function makeId(string $prefix = 'grp'): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }

    public static function getGroopsAll()
    {
        return AdvancedCRUD::select('groop_groups');
    }

    public static function getGroopsId($id, $db = 'groop_groups', $where = 'randomn_id')
    {
        return AdvancedCRUD::select($db, '*', "WHERE {$where} = '" . addslashes((string)$id) . "'");
    }

    public static function createTask($taskData, $db = 'groop_groups')
    {
        return AdvancedCRUD::create($db, $taskData);
    }

    public static function updateTask($taskId, $taskData, $db = 'groop_groups')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE id = {$taskId}");
    }

    public static function del($taskId, $db = 'groop_groups', $where = 'randomn_id')
    {
        return AdvancedCRUD::delete($db, "WHERE {$where} = '" . addslashes((string)$taskId) . "'");
    }

    public static function udape($taskId, $taskData, $db = 'groop_groups')
    {
        return AdvancedCRUD::update($db, $taskData, "WHERE randomn_id = '" . addslashes((string)$taskId) . "'");
    }

    public static function getGroupByRandomId(string $randomnId)
    {
        return AdvancedCRUD::select(
            'groop_groups',
            '*',
            "WHERE randomn_id = '" . addslashes($randomnId) . "' LIMIT 1"
        );
    }

    public static function getGroupsByUserId(int $idUsers)
    {
        return AdvancedCRUD::select(
            'groop_groups',
            '*',
            "WHERE id_users = {$idUsers} ORDER BY id DESC"
        );
    }
    public static function getAttachedQuizzesByConnectId(string $connectId)
    {
        return AdvancedCRUD::select(
            'groop_group_quizzes',
            '*',
            "WHERE connect_id = '" . addslashes($connectId) . "' AND status = 'active' ORDER BY id DESC"
        );
    }

    public static function attachQuizToGroup(array $data)
    {
        return AdvancedCRUD::create('groop_group_quizzes', $data);
    }

    public static function removeQuizFromGroup(string $groupId, int $quizId)
    {
        return AdvancedCRUD::delete(
            'groop_group_quizzes',
            "WHERE connect_id = '" . addslashes($groupId) . "' AND quiz_id = " . (int)$quizId
        );
    }
    public static function createGroup(array $data)
    {
        return AdvancedCRUD::create('groop_groups', $data);
    }

    public static function createMember(array $data)
    {
        return AdvancedCRUD::create('groop_members', $data);
    }

    public static function createInvite(array $data)
    {
        return AdvancedCRUD::create('groop_invites', $data);
    }

    public static function createPost(array $data)
    {
        return AdvancedCRUD::create('groop_posts', $data);
    }

    public static function createReply(array $data)
    {
        return AdvancedCRUD::create('groop_post_replies', $data);
    }

    public static function getMembersByConnectId(string $connectId)
    {
        return AdvancedCRUD::select(
            'groop_members',
            '*',
            "WHERE connect_id = '" . addslashes($connectId) . "' AND status = 'active' ORDER BY id DESC"
        );
    }

    public static function getPostsByConnectId(string $connectId)
    {
        return AdvancedCRUD::select(
            'groop_posts',
            '*',
            "WHERE connect_id = '" . addslashes($connectId) . "' AND status = 'active' ORDER BY id DESC"
        );
    }

    public static function getRepliesByPostId(string $postId)
    {
        return AdvancedCRUD::select(
            'groop_post_replies',
            '*',
            "WHERE post_id = '" . addslashes($postId) . "' AND status = 'active' ORDER BY id ASC"
        );
    }

    public static function getInviteByToken(string $token)
    {
        return AdvancedCRUD::select(
            'groop_invites',
            '*',
            "WHERE invite_token = '" . addslashes($token) . "' LIMIT 1"
        );
    }

    public static function getInvitesByConnectId(string $connectId)
    {
        return AdvancedCRUD::select(
            'groop_invites',
            '*',
            "WHERE connect_id = '" . addslashes($connectId) . "' ORDER BY id DESC"
        );
    }

    public static function getPendingInviteByEmail(string $groupId, string $email)
    {
        return AdvancedCRUD::select(
            'groop_invites',
            '*',
            "WHERE connect_id = '" . addslashes($groupId) . "'
             AND email = '" . addslashes($email) . "'
             AND status = 'pending'
             ORDER BY id DESC LIMIT 1"
        );
    }

    public static function getMembership(string $groupId, int $idUsers)
    {
        return AdvancedCRUD::select(
            'groop_members',
            '*',
            "WHERE connect_id = '" . addslashes($groupId) . "'
             AND id_users = {$idUsers}
             AND status = 'active'
             LIMIT 1"
        );
    }

    public static function incrementLike(string $randomnId)
    {
        $rows = AdvancedCRUD::select(
            'groop_posts',
            '*',
            "WHERE randomn_id = '" . addslashes($randomnId) . "' LIMIT 1"
        );

        if (empty($rows[0])) {
            return false;
        }

        $current = (int)($rows[0]['likes'] ?? 0);
        $newLikes = $current + 1;

        return AdvancedCRUD::update(
            'groop_posts',
            ['likes' => $newLikes],
            "WHERE randomn_id = '" . addslashes($randomnId) . "'"
        );
    }

    public static function deleteInvite(string $inviteId)
    {
        return AdvancedCRUD::delete(
            'groop_invites',
            "WHERE randomn_id = '" . addslashes($inviteId) . "'"
        );
    }

    public static function deleteGroupCascade(string $groupId): void
    {
        AdvancedCRUD::delete('groop_post_replies', "WHERE connect_id = '" . addslashes($groupId) . "'");
        AdvancedCRUD::delete('groop_posts', "WHERE connect_id = '" . addslashes($groupId) . "'");
        AdvancedCRUD::delete('groop_invites', "WHERE connect_id = '" . addslashes($groupId) . "'");
        AdvancedCRUD::delete('groop_members', "WHERE connect_id = '" . addslashes($groupId) . "'");
        AdvancedCRUD::delete('groop_groups', "WHERE randomn_id = '" . addslashes($groupId) . "'");
    }
}