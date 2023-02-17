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
import {SubjectCard} from '#/plugin/forum/resources/forum/data/components/subject-card'

const BlockedSubjectsComponent = (props) =>
  <ListData
    name={`${selectors.STORE_NAME}.moderation.blockedSubjects`}
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
        target: `${props.path}/subjects/show/${rows[0].id}`,
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
    card={SubjectCard}
  />

BlockedSubjectsComponent.propTypes = {
  path: T.string.isRequired,
  forum: T.shape(ForumType.propTypes),
  validateSubject: T.func.isRequired,
  banUser: T.func.isRequired,
  unLockUser: T.func.isRequired
}

const BlockedSubjects = connect(
  state => ({
    path: resourceSelectors.path(state),
    forum: selectors.forum(state)
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
