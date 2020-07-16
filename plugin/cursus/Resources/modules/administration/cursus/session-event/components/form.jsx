import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'

import {trans} from '#/main/app/intl/translation'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {SessionEvent as SessionEventType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionEventFormComponent = (props) =>
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
            name: 'code',
            type: 'string',
            label: trans('code'),
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
            name: 'meta.location',
            type: 'location',
            label: trans('location')
          }, {
            name: 'meta.locationExtra',
            type: 'html',
            label: trans('extra_informations', {}, 'cursus')
          }, {
            name: 'meta.set',
            type: 'string',
            label: trans('session_event_set', {}, 'cursus')
          }, {
            name: 'meta.isEvent',
            type: 'boolean',
            label: trans('show_in_agenda', {}, 'cursus')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.registrationType',
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
            name: 'restrictions.users',
            type: 'number',
            label: trans('users_count'),
            options: {
              min: 0
            },
            displayed: (sessionvent) => sessionvent.registration && constants.REGISTRATION_AUTO !== sessionvent.registration.registrationType
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

SessionEventFormComponent.propTypes = {
  new: T.bool.isRequired,
  sessionEvent: T.shape(SessionEventType.propTypes).isRequired,
  children: T.any
}

const SessionEventForm = connect(
  (state, ownProps) => ({
    new: formSelect.isNew(formSelect.form(state, ownProps.name)),
    sessionEvent: formSelect.data(formSelect.form(state, ownProps.name))
  })
)(SessionEventFormComponent)

export {
  SessionEventForm
}
