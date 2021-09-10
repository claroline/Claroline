import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/plugin/cursus/constants'

const SessionForm = (props) =>
  <FormData
    {...props}
    meta={true}
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
            name: 'restrictions.dates',
            type: 'date-range',
            label: trans('access_dates'),
            required: true
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'description',
            type: 'html',
            label: trans('description')
          }, {
            name: 'plainDescription',
            type: 'string',
            label: trans('plain_description'),
            options: {long: true},
            help: trans('plain_description_help')
          }, {
            name: 'meta.default',
            type: 'boolean',
            label: trans('default_session', {}, 'cursus'),
            help: [
              trans('default_session_help', {}, 'cursus'),
              trans('default_session_help_registration', {}, 'cursus')
            ]
          }, {
            name: 'location',
            type: 'location',
            label: trans('location')
          }, {
            name: 'resources',
            type: 'resources',
            label: trans('resources')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }, {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }, {
            name: 'display.order',
            type: 'number',
            label: trans('order'),
            required: true,
            options: {
              min: 0
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('registration'),
        fields: [
          {
            name: 'registration.selfRegistration',
            type: 'boolean',
            label: trans('activate_self_registration'),
            help: trans('self_registration_training_help', {}, 'cursus'),
            onChange: (checked) => {
              if (!checked) {
                props.update('registration.autoRegistration', false)
                props.update('registration.validation', false)
                props.update('registration.pendingRegistrations', false)
              }
            },
            linked: [
              {
                name: 'registration._selfRegistrationMode',
                type: 'choice',
                label: trans('mode'),
                displayed: (session) => get(session, 'registration.selfRegistration'),
                calculated: (session) => {
                  if (get(session, 'registration.autoRegistration')) {
                    return 'auto'
                  } else if (get(session, 'registration.validation')) {
                    return 'validation'
                  }

                  return 'simple'
                },
                required: true,
                options: {
                  condensed: false,
                  choices: {
                    simple: trans('simple_registration', {}, 'cursus'),
                    validation: trans('validate_registration', {}, 'cursus'),
                    auto: trans('auto_registration', {}, 'cursus')
                  }
                },
                onChange: (registrationMode) => {
                  switch (registrationMode) {
                    case 'simple':
                      props.update('registration.autoRegistration', false)
                      props.update('registration.validation', false)
                      break

                    case 'auto':
                      props.update('registration.autoRegistration', true)

                      // reset incompatible options
                      props.update('restrictions._restrictUsers', false)
                      props.update('restrictions.users', null)
                      props.update('registration.mail', false)
                      props.update('registration.validation', false)
                      props.update('registration.userValidation', false)
                      props.update('registration.selfUnregistration', false)
                      props.update('registration.pendingRegistrations', false)
                      break

                    case 'validation':
                      props.update('registration.validation', true)

                      // reset incompatible options
                      props.update('registration.autoRegistration', false)
                      break
                  }
                }
              }, {
                name: 'registrations.pendingRegistrations',
                type: 'boolean',
                label: trans('enable_session_pending_list', {}, 'cursus'),
                displayed: (session) => get(session, 'registration.selfRegistration')
                  && !get(session, 'registration.autoRegistration')
                  && (get(session, 'restrictions.users') || get(session, 'restrictions._restrictUsers'))
              }
            ]
          }, {
            name: 'registration.mail',
            type: 'boolean',
            label: trans('registration_send_mail', {}, 'cursus'),
            displayed: (session) => !get(session, 'registration.autoRegistration'),
            linked: [
              {
                name: 'registration.userValidation',
                type: 'boolean',
                label: trans('registration_user_validation', {}, 'cursus'),
                help: trans('registration_user_validation_help', {}, 'cursus'),
                displayed: (session) => get(session, 'registration.mail')
              }
            ]
          }, {
            name: 'registration.selfUnregistration',
            type: 'boolean',
            label: trans('activate_self_unregistration'),
            displayed: (session) => !get(session, 'registration.autoRegistration'),
            help: trans('self_unregistration_training_help', {}, 'cursus')
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
        icon: 'fa fa-fw fa-credit-card',
        title: trans('pricing'),
        displayed: param('pricing.enabled'),
        fields: [
          {
            name: 'pricing.price',
            label: trans('price'),
            type: 'currency',
            linked: [
              {
                name: 'pricing.description',
                label: trans('comment'),
                type: 'string',
                options: {
                  long: true
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }, {
            name: 'restrictions._restrictUsers',
            type: 'boolean',
            label: trans('restrict_users_count'),
            calculated: (session) => get(session, 'restrictions.users') || get(session, 'restrictions._restrictUsers'),
            onChange: (value) => {
              if (!value) {
                props.update('restrictions.users', null)
              }
            },
            linked: [
              {
                name: 'restrictions.users',
                type: 'number',
                label: trans('users_count'),
                required: true,
                displayed: (session) => get(session, 'restrictions.users') || get(session, 'restrictions._restrictUsers'),
                options: {
                  min: 0
                }
              }
            ]
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

SessionForm.propTypes = {
  name: T.string.isRequired,
  children: T.any,
  update: T.func.isRequired
}

export {
  SessionForm
}
