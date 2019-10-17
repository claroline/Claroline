import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {constants} from '#/plugin/forum/resources/forum/constants'
import {selectors} from '#/plugin/forum/resources/forum/editor/store'

const EditorComponent = (props) =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    title={trans('parameters')}
    className="content-container"
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(props.forumForm.id)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
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
                displayed: props.forumForm.display.showOverview,
                options: {
                  workspace: props.workspace
                }
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
              choices: Object.keys(listConst.DISPLAY_MODES).map(displayMode => ({
                [displayMode]: listConst.DISPLAY_MODES[displayMode].label
              }))
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
  path: T.string.isRequired,
  workspace: T.object,
  forumForm: T.shape(ForumType.propTypes).isRequired,
  saveForm: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    forumForm: formSelect.data(formSelect.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    saveForm(forumId) {
      dispatch(formActions.saveForm(selectors.FORM_NAME, ['apiv2_forum_update', {id: forumId}]))
    }
  })
)(EditorComponent)

export {
  Editor
}
