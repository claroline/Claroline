import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {select} from '#/plugin/forum/resources/forum/store/selectors'
import {actions} from '#/plugin/forum/resources/forum/store/actions'
import {SubjectCard} from '#/plugin/forum/resources/forum/data/components/subject-card'

const BlockedSubjectsComponent = (props) =>
  <ListData
    name={`${select.STORE_NAME}.moderation.blockedSubjects`}
    fetch={{
      url: ['apiv2_forum_subject_blocked_list', {forum: props.forum.id}],
      autoload: true
    }}
    delete={{
      url: ['apiv2_forum_subject_delete_bulk']
    }}
    display={{
      current: listConst.DISPLAY_LIST
    }}
    definition={[
      {
        name: 'content',
        type: 'string',
        label: trans('message', {}, 'forum'),
        displayed: true,
        primary: true
      }, {
        name: 'title',
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
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-eye',
        label: trans('see_subject', {}, 'forum'),
        target: '/subjects/show/'+rows[0].id,
        scope: ['object']
      },
      // if moderation all => validateMessage
      // if moderation once => validateUser
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check',
        label: trans('validate_subject', {}, 'forum'),
        displayed: props.forum.moderation === 'PRIOR_ALL',
        callback: () => props.validateSubject(rows[0], 'moderation.blockedSubjects')
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
      <SubjectCard
        {...props}
      />
    }
  />


const BlockedSubjects = connect(
  state => ({
    forum: select.forum(state)
  }),
  dispatch => ({
    validateSubject(subject, formName) {
      dispatch(actions.validateSubject(subject, formName))
    },
    banUser(userId, forumId) {
      dispatch(actions.banUser(userId, forumId))
    },
    unLockUser(userId, forumId) {
      dispatch(actions.unLockUser(userId, forumId))
    }
  })
)(BlockedSubjectsComponent)

export {
  BlockedSubjects
}
