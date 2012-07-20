<?php

class Ragtek_MM_Helper{


    /**
     *  helper to create a post
     * @static
     * @param array $user
     * @param $threadId
     * @param $message
     * @param string $state
     * @return array
     */
    public static function createPost(array $user, $threadId, $message, $state = 'visible')
    {
        /** @var $threadModel XenForo_Model_Thread */
        $threadModel = XenForo_Model::create('XenForo_Model_Thread');
        $thread = $threadModel->getThreadById($threadId);

        $writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
        $writer->set('user_id', $user['user_id']);
        $writer->set('username', $user['username']);
        $writer->set('message', $message);
        $writer->set('message_state', $state);
        $writer->set('thread_id', $thread['thread_id']);
        $writer->save();
        $post = $writer->getMergedData();

        return $post;
    }
}