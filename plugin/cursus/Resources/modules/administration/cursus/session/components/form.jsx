import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/plugin/cursus/administration/cursus/constants'

const SessionForm = (props) =>
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
            name: 'meta.default',
            type: 'boolean',
            label: trans('default_session', {}, 'cursus')
          }, {
            name: 'meta.course.title',
            type: 'string',
            label: trans('course', {}, 'cursus'),
            required: true,
            disabled: true
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
            name: 'registration.publicRegistration',
            type: 'boolean',
            label: trans('public_registration')
          }, {
            name: 'registration.publicUnregistration',
            type: 'boolean',
            label: trans('public_unregistration')
          }, {
            name: 'registration.registrationValidation',
            type: 'boolean',
            label: trans('registration_validation', {}, 'cursus')
          }, {
            name: 'registration.userValidation',
            type: 'boolean',
            label: trans('user_validation', {}, 'cursus')
          }, {
            name: 'registration.organizationValidation',
            type: 'boolean',
            label: trans('organization_validation', {}, 'cursus')
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
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions._restrictUsers',
            type: 'boolean',
            label: trans('restrict_users_count'),
            calculated: (course) => get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
            onChange: (value) => {
              if (!value) {
                props.update(props.name, 'restrictions.users', null)
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
