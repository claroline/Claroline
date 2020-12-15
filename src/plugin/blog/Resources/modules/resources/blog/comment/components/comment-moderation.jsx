import React from 'react'
import {CommentCard} from '#/plugin/blog/resources/blog/comment/components/comment.jsx'

const CommentModerationCard = props =>
  <CommentCard
    {...props}
    showEdit={false}
    showGoToPost={true}
  />

export {CommentModerationCard}