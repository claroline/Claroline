import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {constants as listConst} from '#/main/app/content/list/constants'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {Subject as SubjectType} from '#/plugin/forum/resources/forum/player/prop-types'
import {selectors} from '#/plugin/forum/resources/forum/store'
import {actions} from '#/plugin/forum/resources/forum/player/store'
import {SubjectCard} from '#/plugin/forum/resources/forum/data/components/subject-card'

const SubjectsList = props =>
  <div>
    <h2>{trans('subjects', {}, 'forum')}</h2>
    <ListData
      name={`${selectors.STORE_NAME}.subjects.list`}
      fetch={{
        url: ['apiv2_forum_list_subjects', {id: props.forum.id}],
        autoload: true
      }}
      delete={{
        url: ['apiv2_forum_subject_delete_bulk'],
        displayed: (rows) => props.currentUser && ((rows[0].meta.creator.id === props.currentUser.id) || props.moderator)
      }}
      primaryAction={(subject) => ({
        type: LINK_BUTTON,
        target: `${props.path}/subjects/show/${subject.id}`,
        label: trans('open', {}, 'actions')
      })}
      display={{
        current: props.forum.display.subjectDataList || listConst.DISPLAY_LIST
      }}
      definition={[
        {
          name: 'title',
          type: 'string',
          label: trans('title'),
          displayed: true,
          primary: true
        }, {
          name: 'meta.closed',
          alias: 'closed',
          type: 'boolean',
          label: trans('closed_subject', {}, 'forum'),
          displayed: true,
          //filterable true if we want them
          filterable: false
        }, {
          name: 'meta.sticky',
          alias: 'sticked',
          type: 'boolean',
          label: trans('stuck', {}, 'forum'),
          displayed: true,
          //filterable true if we want them
          filterable: false
        }, {
          name: 'meta.hot',
          type: 'boolean',
          label: trans('hot_subject', {}, 'forum'),
          filterable: false,
          displayed: true,
          sortable: false
        }, {
          name: 'meta.messages',
          type: 'number',
          label: trans('posts_count', {}, 'forum'),
          displayed: true,
          filterable: false,
          sortable: true
        }, {
          name: 'meta.updated',
          type: 'date',
          label: trans('last_modification'),
          displayed: true,
          filterable: false,
          sortable: false,
          options: {
            time: true
          }
        }, {
          name: 'meta.creator',
          type: 'user',
          label: trans('creator'),
          displayed: true,
          filterable: true,
          alias: 'creator'
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          displayable: false,
          filterable: true,
          sortable: false,
          options: {
            objectClass: 'Claroline\\ForumBundle\\Entity\\Subject'
          }
        }, {
          name: 'createdBefore',
          type: 'date',
          label: trans('created_before'),
          displayed: false,
          displayable: false,
          filterable: true,
          sortable: false
        }, {
          name: 'createdAfter',
          type: 'date',
          label: trans('created_after'),
          displayed: false,
          displayable: false,
          filterable: true,
          sortable: false
        }, {
          name: 'lastMessage',
          type: 'string',
          label: trans('last_message', {}, 'forum'),
          displayed: false,
          displayable: true,
          filterable: false,
          sortable: true
        }
      ]}
      actions={(rows) => [
        {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${props.path}/subjects/form/${rows[0].id}`,
          scope: ['object'],
          displayed: props.currentUser && rows[0].meta.creator.id === props.currentUser.id
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-thumb-tack',
          label: trans('stick', {}, 'forum'),
          callback: () => props.stickSubject(rows[0]),
          displayed: !rows[0].meta.sticky && props.moderator
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-thumb-tack',
          label: trans('unstick', {}, 'forum'),
          callback: () => props.unStickSubject(rows[0]),
          displayed: rows[0].meta.sticky && props.moderator
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-flag',
          label: trans('flag', {}, 'forum'),
          displayed: !rows[0].meta.flagged && props.currentUser && rows[0].meta.creator.id !== props.currentUser.id,
          callback: () => props.flagSubject(rows[0]),
          scope: ['object']
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-flag',
          label: trans('unflag', {}, 'forum'),
          displayed: rows[0].meta.flagged && props.currentUser && rows[0].meta.creator.id !== props.currentUser.id,
          callback: () => props.unFlagSubject(rows[0]),
          scope: ['object']
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-circle-xmark',
          label: trans('close_subject', {}, 'forum'),
          callback: () => props.closeSubject(rows[0]),
          displayed: !rows[0].meta.closed && props.currentUser && (rows[0].meta.creator.id === props.currentUser.id || props.moderator)
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-circle-check',
          label: trans('open_subject', {}, 'forum'),
          callback: () => props.unCloseSubject(rows[0]),
          displayed: rows[0].meta.closed && props.currentUser && (rows[0].meta.creator.id === props.currentUser.id || props.moderator)
        }
      ]}
      card={SubjectCard}
    />
  </div>

SubjectsList.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  forum: T.shape(ForumType.propTypes),
  subject: T.shape(SubjectType.propTypes),
  moderator: T.object,
  data: T.object,
  stickSubject: T.func.isRequired,
  unStickSubject: T.func.isRequired,
  closeSubject: T.func.isRequired,
  unCloseSubject: T.func.isRequired,
  flagSubject: T.func.isRequired,
  unFlagSubject: T.func.isRequired
}

const Subjects = connect(
  state => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    forum: selectors.forum(state),
    subject: selectors.subject(state),
    moderator: selectors.moderator(state)
  }),
  dispatch => ({
    stickSubject(subject) {
      dispatch(actions.stickSubject(subject))
    },
    unStickSubject(subject) {
      dispatch(actions.unStickSubject(subject))
    },
    closeSubject(subject) {
      dispatch(actions.closeSubject(subject))
    },
    unCloseSubject(subject) {
      dispatch(actions.unCloseSubject(subject))
    },
    flagSubject(subject) {
      dispatch(actions.flagSubject(subject))
    },
    unFlagSubject(subject) {
      dispatch(actions.unFlagSubject(subject))
    }
  })
)(SubjectsList)

export {
  Subjects
}
