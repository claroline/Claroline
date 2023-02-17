import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {actions, selectors} from '#/plugin/forum/resources/forum/store'
import {MessageCard} from '#/plugin/forum/resources/forum/data/components/message-card'

const BlockedMessagesComponent = (props) =>
  <ListData
    name={`${selectors.STORE_NAME}.moderation.blockedMessages`}
    fetch={{
      url: ['apiv2_forum_message_blocked_list', {forum: props.forum.id}],
      autoload: true
    }}
    delete={{
      url: ['apiv2_forum_message_delete_bulk']
    }}
    display={{
      current: listConst.DISPLAY_LIST
    }}
    definition={[
      {
        name: 'content',
        type: 'string',
        label: trans('message'),
        displayed: true,
        primary: true
      }, {
        name: 'subject.title',
        type: 'string',
        label: trans('subject_title', {}, 'forum'),
        displayed: true
      }, {
        name: 'meta.creator',
        type: 'user',
        label: trans('creator'),
        displayed: true
      }, {
        name: 'meta.updated',
        type: 'date',
        label: trans('last_modification'),
        displayed: true,
        option: {
          time: true
        }
      }
    ]}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-eye',
        label: trans('see_subject', {}, 'forum'),
        target: `${props.path}/subjects/show/${rows[0].subject.id}`,
        scope: ['object']
      },
      // if moderation all => validateMessage
      // if moderation once => validateUser
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check',
        label: trans('validate_message', {}, 'forum'),
        displayed: props.forum.moderation === 'PRIOR_ALL',
        callback: () => props.validateMessage(rows[0], rows[0].subject.id, 'moderation.blockedMessages')
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check',
        label: trans('validate_user', {}, 'forum'),
        displayed: props.forum.moderation === 'PRIOR_ONCE',
        callback: () => props.unLockUser(rows[0].meta.creator.id, props.forum.id)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-times',
        label: trans('block_user', {}, 'forum'),
        displayed: props.forum.moderation === 'PRIOR_ONCE',
        callback: () => props.banUser(rows[0].meta.creator.id, props.forum.id)
      }
    ]}
    card={(props) =>
      <MessageCard
        {...props}
      />
    }
  />

BlockedMessagesComponent.propTypes = {
  path: T.string.isRequired,
  forum: T.shape(ForumType.propTypes),
  subject: T.object,
  validateMessage: T.func.isRequired,
  banUser: T.func.isRequired,
  unLockUser: T.func.isRequired
}

const BlockedMessages = connect(
  state => ({
    path: resourceSelectors.path(state),
    forum: selectors.forum(state),
    subject: selectors.subject(state)
  }),
  dispatch => ({
    validateMessage(message, subjectId, formName) {
      dispatch(actions.validatePost(message, subjectId, formName))
    },
    banUser(userId, forumId) {
      dispatch(actions.banUser(userId, forumId))
    },
    unLockUser(userId, forumId) {
      dispatch(actions.unLockUser(userId, forumId))
    }
  })
)(BlockedMessagesComponent)

export {
  BlockedMessages
}
