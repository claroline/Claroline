import React from 'react'
// import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {constants as listConst} from '#/main/core/data/list/constants'

import {select} from '#/plugin/forum/resources/forum/selectors'
import {actions} from '#/plugin/forum/resources/forum/actions'
import {MessageCard} from '#/plugin/forum/resources/forum/data/components/message-card'

const BlockedMessagesComponent = (props) =>
  <div>
    <h2>{trans('moderated_posts', {}, 'forum')}</h2>
    <DataListContainer
      name="moderation.blockedMessages"
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
          name: 'meta.creator.username',
          type: 'string',
          label: trans('creator'),
          displayed: true,
          searchable: false
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
          type: 'link',
          icon: 'fa fa-fw fa-eye',
          label: trans('see_subject', {}, 'forum'),
          target: '/subjects/show/'+rows[0].subject.id,
          context: 'row'
        },
        // if moderation all => validateMessage
        // if moderation once => validateUser
        {
          type: 'callback',
          icon: 'fa fa-fw fa-check',
          label: trans('validate_message', {}, 'forum'),
          displayed: props.forum.moderation === 'PRIOR_ALL',
          callback: () => props.validateMessage(rows[0], rows[0].subject.id)
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-check',
          label: trans('validate_user', {}, 'forum'),
          displayed: props.forum.moderation === 'PRIOR_ONCE',
          callback: () => props.unLockUser(rows[0].meta.creator.id, props.forum.id)
        }, {
          type: 'callback',
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
  </div>


const BlockedMessages = connect(
  state => ({
    forum: select.forum(state),
    subject: select.subject(state)
  }),
  dispatch => ({
    validateMessage(message, subjectId) {
      dispatch(actions.validateMessage(message, subjectId))
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
