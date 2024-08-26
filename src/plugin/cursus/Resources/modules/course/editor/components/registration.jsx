import React from 'react'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {FormParameters} from '#/main/app/content/form/parameters/containers/main'

const CourseEditorRegistration = (props) =>
  <EditorPage
    title={trans('registration')}
    help={trans('course_registration_help', {}, 'cursus')}
    definition={[
      {
        title: trans('registration'),
        primary: true,
        fields: [
          {
            name: 'registration.selfRegistration',
            type: 'boolean',
            label: trans('activate_self_registration'),
            help: trans('self_registration_training_help', {}, 'cursus'),
            onChange: (checked) => {
              if (!checked) {
                props.update(props.name, 'registration.autoRegistration', false)
                props.update(props.name, 'registration.validation', false)
                props.update(props.name, 'registration.pendingRegistrations', false)
              }
            },
            linked: [
              {
                name: 'registration._selfRegistrationMode',
                type: 'choice',
                label: trans('mode'),
                displayed: (course) => get(course, 'registration.selfRegistration'),
                calculated: (course) => {
                  if (get(course, 'registration.autoRegistration')) {
                    return 'auto'
                  } else if (get(course, 'registration.validation')) {
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
                      props.update(props.name, 'registration.autoRegistration', false)
                      props.update(props.name, 'registration.validation', false)
                      break

                    case 'auto':
                      props.update(props.name, 'registration.autoRegistration', true)

                      // reset incompatible options
                      props.update(props.name, 'restrictions._restrictUsers', false)
                      props.update(props.name, 'restrictions.users', null)
                      props.update(props.name, 'registration.mail', false)
                      props.update(props.name, 'registration.validation', false)
                      props.update(props.name, 'registration.userValidation', false)
                      props.update(props.name, 'registration.selfUnregistration', false)
                      props.update(props.name, 'registration.pendingRegistrations', false)
                      props.update(props.name, 'registration._enableCustomForm', false)
                      props.update(props.name, 'registration.form', [])
                      break

                    case 'validation':
                      props.update(props.name, 'registration.validation', true)

                      // reset incompatible options
                      props.update(props.name, 'registration.autoRegistration', false)
                      break
                  }
                }
              }, {
                name: 'registration.pendingRegistrations',
                type: 'boolean',
                label: trans('enable_course_pending_list', {}, 'cursus'),
                displayed: (course) => get(course, 'registration.selfRegistration') && !get(course, 'registration.autoRegistration')
              }
            ]
          }, {
            name: 'registration.mail',
            type: 'boolean',
            label: trans('registration_send_mail', {}, 'cursus'),
            displayed: (course) => !get(course, 'registration.autoRegistration'),
            onChange: (checked) => {
              if (!checked) {
                props.update(props.name, 'registration.userValidation', false)
              }
            },
            linked: [
              {
                name: 'registration.userValidation',
                type: 'boolean',
                label: trans('registration_user_validation', {}, 'cursus'),
                help: trans('registration_user_validation_help', {}, 'cursus'),
                displayed: (course) => get(course, 'registration.mail')
              }
            ]
          }, {
            name: 'registration.selfUnregistration',
            type: 'boolean',
            label: trans('activate_self_unregistration'),
            help: trans('self_unregistration_training_help', {}, 'cursus'),
            displayed: (course) => !get(course, 'registration.autoRegistration')
          }, {
            name: 'registration._enableCustomForm',
            type: 'boolean',
            label: trans('enable_custom_registration_form', {}, 'cursus'),
            displayed: (data) => !get(data, 'registration.autoRegistration', false),
            calculated: (data) => get(data, 'registration._enableCustomForm') || !isEmpty(get(data, 'registration.form')),
            onChange: (enabled) => {
              if (!enabled) {
                props.update(props.name, 'registration.form', [])
              }
            },
            help: [trans('custom_registration_form_help', {}, 'cursus'), trans('custom_registration_form_group_help', {}, 'cursus')]
          }
        ]
      }
    ]}
  >
    {(get(props.course, 'registration._enableCustomForm') || !isEmpty(get(props.course, 'registration.form'))) &&
      <FormParameters
        name={props.name}
        dataPart="registration.form"
        sections={get(props.course, 'registration.form', [])}
      />
    }
  </EditorPage>

export {
  CourseEditorRegistration
}
