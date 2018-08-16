import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {Forum as ForumType} from '#/plugin/forum/resources/forum/prop-types'
import {constants} from '#/plugin/forum/resources/forum/constants'
import {selectors} from '#/plugin/forum/resources/forum/editor/store'

const EditorComponent = (props) =>
  <FormData
    level={3}
    displayLevel={2}
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
