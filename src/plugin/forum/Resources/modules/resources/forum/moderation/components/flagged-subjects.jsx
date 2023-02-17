import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {selectors} from '#/plugin/forum/resources/forum/store'
import {actions} from '#/plugin/forum/resources/forum/player/store'
import {SubjectCard} from '#/plugin/forum/resources/forum/data/components/subject-card'

const FlaggedSubjectsComponent = (props) =>
  <ListData
    name={`${selectors.STORE_NAME}.moderation.flaggedSubjects`}
    fetch={{
      url: ['apiv2_forum_subject_flagged_list', {forum: props.forum.id}],
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
        label: trans('message'),
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
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-flag',
        label: trans('unflag', {}, 'forum'),
        displayed: true,
        callback: () => props.unFlagSubject(rows[0])
      }
    ]}
    card={SubjectCard}
  />

FlaggedSubjectsComponent.propTypes = {
  path: T.string.isRequired,
  forum: T.shape(ForumType.propTypes),
  subject: T.object,
  data: T.object,
  unFlagSubject: T.func.isRequired
}

const FlaggedSubjects = connect(
  state => ({
    path: resourceSelectors.path(state),
    forum: selectors.forum(state),
    subject: selectors.subject(state)
  }),
  dispatch => ({
    unFlagSubject(subject) {
      dispatch(actions.unFlag(subject))
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.moderation.flaggedSubjects`))
    }
  })
)(FlaggedSubjectsComponent)

export {
  FlaggedSubjects
}
