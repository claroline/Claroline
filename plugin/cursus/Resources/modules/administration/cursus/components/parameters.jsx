import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {trans} from '#/main/app/intl/translation'

import {actions, selectors} from '#/plugin/cursus/administration/cursus/store'
import {Parameters as ParametersType} from '#/plugin/cursus/administration/cursus/prop-types'

const ParametersComponent = (props) =>
  <FormData
    level={3}
    name={selectors.STORE_NAME + '.parametersForm'}
    buttons={true}
    save={{
      type: CALLBACK_BUTTON,
      callback: () => props.saveForm(props.parameters)
    }}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-cogs',
        title: trans('general'),
        defaultOpened: true,
        fields: [
          {
            name: 'cursus.disable_invitations',
            type: 'boolean',
            label: trans('disable_invitations', {}, 'cursus')
          }, {
            name: 'cursus.disable_certificates',
            type: 'boolean',
            label: trans('disable_certificates', {}, 'cursus')
          }, {
            name: 'cursus.disable_session_event_registration',
            type: 'boolean',
            label: trans('disable_session_event_registration', {}, 'cursus')
          }, {
            name: 'cursus.session_default_total',
            type: 'number',
            label: trans('session_default_total', {}, 'cursus'),
            options: {
              min: 0
            }
          }, {
            name: 'cursus.session_default_duration',
            type: 'number',
            label: trans('default_session_duration_label', {}, 'cursus'),
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  />

ParametersComponent.propTypes = {
  path: T.string.isRequired,
  parameters: T.shape(ParametersType.propTypes).isRequired,
  saveForm: T.func.isRequired
}

const Parameters = connect(
  (state) => ({
    parameters: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.parametersForm'))
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
