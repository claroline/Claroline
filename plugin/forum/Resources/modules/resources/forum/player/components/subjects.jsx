import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {constants as listConst} from '#/main/core/data/list/constants'
import {currentUser} from '#/main/core/user/current'

import {select} from '#/plugin/forum/resources/forum/selectors'
import {actions} from '#/plugin/forum/resources/forum/player/actions'
import {SubjectCard} from '#/plugin/forum/resources/forum/data/components/subject-card'

const authenticatedUser = currentUser()

const SubjectsList = props =>
  <div>
    <h2>{trans('subjects', {}, 'forum')}</h2>
    <DataListContainer
      name="subjects.list"
      fetch={{
        url: ['claroline_forum_api_forum_getsubjects', {id: props.forum.id}],
        autoload: true
      }}
      delete={{
        url: ['apiv2_forum_subject_delete_bulk'],
        displayed: (rows) => (rows[0].meta.creator.id === authenticatedUser.id) || props.moderator
      }}
      primaryAction={(subject) => ({
        type: 'link',
        target: '/subjects/show/'+subject.id,
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
          option: {
            time: true
          }
        }, {
          name: 'meta.creator.username',
          type: 'string',
          label: trans('creator'),
          displayed: true,
          searchable: true,
          filterable: true,
          alias: 'creator'
        }, {
          name: 'tags',
          type: 'string',
          label: trans('tags'),
          renderer: (rowData) => rowData.tags.map(tag =>
            <span key={tag}>{tag} </span>
          ),
          displayed: true,
          sortable: false
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
          type: 'link',
          icon: 'fa fa-fw fa-eye',
          label: trans('see_subject', {}, 'forum'),
          target: '/subjects/show/'+rows[0].id,
          context: 'row'
        }, {
          type: 'link',
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit'),
          target: '/subjects/form/'+rows[0].id,
          context: 'row',
          displayed: rows[0].meta.creator.id === authenticatedUser.id
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-thumb-tack',
          label: trans('stick', {}, 'forum'),
          callback: () => props.stickSubject(rows[0]),
          displayed: !rows[0].meta.sticky && props.moderator
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-thumb-tack',
          label: trans('unstick', {}, 'forum'),
          callback: () => props.unStickSubject(rows[0]),
          displayed: rows[0].meta.sticky && props.moderator
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-flag-o',
          label: trans('flag', {}, 'forum'),
          displayed: !rows[0].meta.flagged && (rows[0].meta.creator.id !== authenticatedUser.id),
          callback: () => props.flagSubject(rows[0]),
          context: 'row'
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-flag',
          label: trans('unflag', {}, 'forum'),
          displayed: rows[0].meta.flagged && rows[0].meta.creator.id !== authenticatedUser.id,
          callback: () => props.unFlagSubject(rows[0]),
          context: 'row'
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-times-circle-o',
          label: trans('close_subject', {}, 'forum'),
          callback: () => props.closeSubject(rows[0]),
          displayed: !rows[0].meta.closed && (rows[0].meta.creator.id === authenticatedUser.id || props.moderator)
        }, {
          type: 'callback',
          icon: 'fa fa-fw fa-check-circle-o',
          label: trans('open_subject', {}, 'forum'),
          callback: () => props.unCloseSubject(rows[0]),
          displayed: rows[0].meta.closed && (rows[0].meta.creator.id === authenticatedUser.id || props.moderator)
        }
      ]}
      card={(props) =>
        <SubjectCard
          {...props}
          contentText={props.data.content}
        />
      }
    />
  </div>


const Subjects = connect(
  state => ({
    forum: select.forum(state),
    subject: select.subject(state),
    moderator: select.moderator(state)
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
