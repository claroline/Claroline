import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {trans} from '#/main/core/translation'

import {actions} from '#/plugin/cursus/administration/cursus/parameters/store'
import {Parameters as ParametersType} from '#/plugin/cursus/administration/cursus/prop-types'

const ParametersComponent = (props) =>
  <FormData
    level={3}
    name="parametersForm"
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(props.parameters)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-cogs',
        title: trans('general'),
        defaultOpened: true,
        fields: [
          {
            name: 'disable_invitations',
            type: 'boolean',
            label: trans('disable_invitations', {}, 'cursus'),
            required: true
          }, {
            name: 'disable_certificates',
            type: 'boolean',
            label: trans('disable_certificates', {}, 'cursus'),
            required: true
          }, {
            name: 'disable_session_event_registration',
            type: 'boolean',
            label: trans('disable_session_event_registration', {}, 'cursus'),
            required: true
          }, {
            name: 'enable_courses_profile_tab',
            type: 'boolean',
            label: trans('enable_courses_tab_in_profile', {}, 'cursus'),
            required: true
          }, {
            name: 'enable_ws_in_courses_profile_tab',
            type: 'boolean',
            label: trans('enable_workspace_in_courses_tab_in_profile', {}, 'cursus'),
            required: true
          }, {
            name: 'session_default_total',
            type: 'number',
            label: trans('session_default_total', {}, 'cursus'),
            options: {
              min: 0
            }
          }, {
            name: 'session_default_duration',
            type: 'number',
            label: trans('default_session_duration_label', {}, 'cursus'),
            options: {
              min: 0
            }
          }, {
            name: 'display_user_events_in_desktop_agenda',
            type: 'boolean',
            label: trans('display_user_events_in_desktop_agenda', {}, 'cursus'),
            required: true
          }
        ]
      }
    ]}
  />

ParametersComponent.propTypes = {
  parameters: T.shape(ParametersType.propTypes).isRequired,
  saveForm: T.func.isRequired
}

const Parameters = connect(
  (state) => ({
    parameters: formSelect.data(formSelect.form(state, 'parametersForm'))
  }),
  (dispatch) => ({
    saveForm(parameters) {
      dispatch(actions.saveParameters(parameters))
    }
  })
)(ParametersComponent)

export {
  Parameters
}
