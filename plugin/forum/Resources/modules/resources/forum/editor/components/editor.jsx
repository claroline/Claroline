import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {constants} from '#/plugin/forum/resources/forum/constants'

const EditorComponent = (props) =>
  <FormContainer
    level={3}
    displayLevel={2}
    name="forumForm"
    title={trans('parameters')}
    className="content-container"
    buttons={true}
    save={{
      type: 'callback',
      callback: () => props.saveForm(props.forumForm.id)
    }}
    cancel={{
      type: 'link',
      target: '/',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-home',
        title: trans('overview'),
        fields: [
          {
            name: 'display.showOverview',
            type: 'boolean',
            label: trans('show_overview', {}, 'forum'),
            linked: [
              {
                name: 'display.description',
                type: 'html',
                label: trans('overview_message', {}, 'forum'),
                displayed: props.forumForm.display.showOverview
              },
              {
                name: 'display.lastMessagesCount',
                type: 'number',
                label: trans('show_last_messages', {}, 'forum'),
                displayed: props.forumForm.display.showOverview
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.subjectDataList',
            type: 'choice',
            label: trans('subjects_list_display', {}, 'forum'),
            options: {
              noEmpty: true,
              choices: constants.LIST_DISPLAY_MODES
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('edition_restriction', {}, 'forum'),
        fields: [
          {
            name: 'restrictions.lockForum',
            label: trans('restrict_by_dates'),
            type: 'boolean',
            linked: [
              {
                name: 'restrictions.lockDate',
                type: 'date',
                label: trans('date'),
                displayed: props.forumForm.restrictions.lockForum,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-gavel',
        title: trans('moderation', {}, 'forum'),
        fields: [
          {
            name: 'moderation',
            type: 'choice',
            label: trans('moderation_type', {}, 'forum'),
            options: {
              noEmpty: true,
              choices: constants.MODERATION_MODES
            }
          }
        ]
      }
    ]}
  />

EditorComponent.propTypes = {
  forumForm: T.shape(ForumType.propTypes).isRequired,
  saveForm: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    forumForm: formSelect.data(formSelect.form(state, 'forumForm'))
  }),
  (dispatch) => ({
    saveForm(forumId) {
      dispatch(formActions.saveForm('forumForm', ['apiv2_forum_update', {id: forumId}]))
    }
  })
)(EditorComponent)

export {
  Editor
}
