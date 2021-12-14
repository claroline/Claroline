import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

const CourseForm = (props) =>
  <FormData
    name={props.name}
    meta={true}
    buttons={true}
    target={(data, isNew) => isNew ?
      ['apiv2_cursus_course_create'] :
      ['apiv2_cursus_course_update', {id: data.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.isNew ? props.path : route(props.path, props.course),
      exact: true
    }}
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
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'parent',
            type: 'course',
            label: trans('parent')
          }, {
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
            name: 'meta.duration',
            type: 'number',
            label: trans('duration'),
            required: true,
            options: {
              min: 0,
              unit: trans('days')
            }
          }, {
            name: 'tags',
            label: trans('tags'),
            type: 'tag'
          }, {
            name: 'organizations',
            type: 'organizations',
            label: trans('organizations')
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
          }, {
            name: 'display.hideSessions',
            type: 'boolean',
            label: trans('hide_sessions', {}, 'cursus')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('opening_parameters'),
        fields: [
          {
            name: 'opening.session',
            label: trans('opening_session', {}, 'cursus'),
            type: 'choice',
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                none: trans('opening_session_none', {}, 'cursus'),
                first_available: trans('opening_session_first_available', {}, 'cursus'),
                default: trans('opening_session_default', {}, 'cursus')
              }
            },
            help: trans('opening_session_help', {}, 'cursus')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('registration'),
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
            name: 'registration.propagate',
            type: 'boolean',
            label: trans('propagate_registration', {}, 'cursus'),
            help: trans('propagate_registration_help', {}, 'cursus')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-calendar-week',
        title: trans('training_sessions', {}, 'cursus'),
        fields: [
          {
            name: 'workspace',
            type: 'workspace',
            label: trans('workspace'),
            displayed: (course) => course.workspace || !course.workspaceModel
          }, {
            name: 'workspaceModel',
            type: 'workspace',
            label: trans('workspace_model'),
            options: {
              picker: {
                model: true,
                title: trans('workspace_model')
              }
            },
            displayed: (course) => course.workspaceModel || !course.workspace
          }, {
            name: 'meta.tutorRoleName',
            type: 'choice',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => course.workspace && course.workspace.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.workspace && props.course.workspace.roles ?
                props.course.workspace.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
            name: 'meta.learnerRoleName',
            type: 'choice',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => course.workspace && course.workspace.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.workspace && props.course.workspace.roles ?
                props.course.workspace.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
            name: 'meta.tutorRoleName',
            type: 'choice',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => !course.workspace && course.workspaceModel && course.workspaceModel.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.workspaceModel && props.course.workspaceModel.roles ?
                props.course.workspaceModel.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
            }
          }, {
            name: 'meta.learnerRoleName',
            type: 'choice',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => !course.workspace && course.workspaceModel && course.workspaceModel.roles,
            required: true,
            options: {
              condensed: true,
              multiple: false,
              choices: props.course && props.course.workspaceModel && props.course.workspaceModel.roles ?
                props.course.workspaceModel.roles.reduce((acc, role) => {
                  if (2 === role.type) {
                    acc[role.translationKey] = trans(role.translationKey)
                  }

                  return acc
                }, {}) :
                {}
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
            calculated: (course) => !!get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
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
  />

CourseForm.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,

  // from store
  isNew: T.bool.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  update: T.func.isRequired
}

export {
  CourseForm
}