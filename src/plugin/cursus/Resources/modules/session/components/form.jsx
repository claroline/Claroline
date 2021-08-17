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
            name: 'meta.order',
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
            linked: [
              {
                name: 'registration.validation',
                type: 'boolean',
                label: trans('validate_registration'),
                help: trans('validate_registration_help', {}, 'cursus'),
                displayed: (course) => course.registration && course.registration.selfRegistration
              }
            ]
          }, {
            name: 'registration.selfUnregistration',
            type: 'boolean',
            label: trans('activate_self_unregistration'),
            help: trans('self_unregistration_training_help', {}, 'cursus')
          }, {
            name: 'registration.mail',
            type: 'boolean',
            label: trans('registration_send_mail', {}, 'cursus'),
            linked: [
              {
                name: 'registration.userValidation',
                type: 'boolean',
                label: trans('registration_user_validation', {}, 'cursus'),
                help: trans('registration_user_validation_help', {}, 'cursus'),
                displayed: (course) => course.registration && course.registration.mail
              }
            ]
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
            name: 'restrictions._restrictUsers',
            type: 'boolean',
            label: trans('restrict_users_count'),
            calculated: (course) => get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
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
                displayed: (course) => get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
                options: {
                  min: 0
                }
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-chart-pie',
        title: trans('quotas'),
        displayed: param('quotas.enabled'),
        fields: [
          {
            name: 'quotas.used',
            label: trans('used_by_quotas'),
            type: 'boolean'
          },
          {
            name: 'quotas.days',
            type: 'number',
            label: trans('quota_days', {}, 'cursus'),
            options: {
              min: 0,
              unit: trans('days')
            }
          },
          {
            name: 'quotas.hours',
            type: 'number',
            label: trans('quota_hours', {}, 'cursus'),
            options: {
              min: 0,
              max: 24,
              unit: trans('hours')
            }
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
