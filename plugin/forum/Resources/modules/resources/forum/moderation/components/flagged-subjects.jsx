import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {actions as listActions} from '#/main/app/content/list/store'

import {actions} from '#/plugin/forum/resources/forum/player/actions'
import {select} from '#/plugin/forum/resources/forum/selectors'
import {SubjectCard} from '#/plugin/forum/resources/forum/data/components/subject-card'

const FlaggedSubjectsComponent = (props) =>
  <ListData
    name="moderation.flaggedSubjects"
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
        context: 'row'
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-flag',
        label: trans('unflag', {}, 'forum'),
        displayed: true,
        callback: () => props.unFlagSubject(rows[0])
      }
    ]}
    card={(props) =>
      <SubjectCard
        {...props}
        contentText={props.data.content}
      />
    }
  />


const FlaggedSubjects = connect(
  state => ({
    forum: select.forum(state),
    subject: select.subject(state)
  }),
  dispatch => ({
    unFlagSubject(subject) {
      dispatch(actions.unFlag(subject))
      dispatch(listActions.invalidateData('moderation.flaggedSubjects'))
    }
  })
)(FlaggedSubjectsComponent)

export {
  FlaggedSubjects
}
