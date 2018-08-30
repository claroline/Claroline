import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'

import {trans} from '#/main/core/translation'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {Session as SessionType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionFormComponent = (props) =>
  <FormData
    {...props}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-cogs',
        title: trans('parameters'),
        fields: [
          {
            name: 'meta.defaultSession',
            type: 'boolean',
            label: trans('default_session', {}, 'cursus'),
            required: true
          }, {
            name: 'meta.order',
            type: 'number',
            label: trans('order'),
            required: true,
            options: {
              min: 0
            }
          }, {
            name: 'meta.color',
            type: 'color',
            label: trans('color')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.publicRegistration',
            type: 'boolean',
            label: trans('public_registration'),
            required: true
          }, {
            name: 'registration.publicUnregistration',
            type: 'boolean',
            label: trans('public_unregistration'),
            required: true
          }, {
            name: 'registration.registrationValidation',
            type: 'boolean',
            label: trans('registration_validation', {}, 'cursus'),
            required: true
          }, {
            name: 'registration.userValidation',
            type: 'boolean',
            label: trans('user_validation', {}, 'cursus'),
            required: true
          }, {
            name: 'registration.organizationValidation',
            type: 'boolean',
            label: trans('organization_validation', {}, 'cursus'),
            required: true
          }, {
            name: 'registration.eventRegistrationType',
            type: 'choice',
            label: trans('session_event_registration', {}, 'cursus'),
            required: true,
            options: {
              multiple: false,
              choices: constants.REGISTRATION_TYPES
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('restrictions'),
        fields: [
          {
            name: 'restrictions.dates',
            type: 'date-range',
            label: trans('access_dates'),
            required: true,
            options: {
              time: true
            }
          }, {
            name: 'restrictions.maxUsers',
            type: 'number',
            label: trans('maxUsers'),
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

SessionFormComponent.propTypes = {
  new: T.bool.isRequired,
  session: T.shape(SessionType.propTypes).isRequired,
  children: T.any
}

const SessionForm = connect(
  (state, ownProps) => ({
    new: formSelect.isNew(formSelect.form(state, ownProps.name)),
    session: formSelect.data(formSelect.form(state, ownProps.name))
  })
)(SessionFormComponent)

export {
  SessionForm
}
