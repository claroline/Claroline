import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

const CourseParameters = (props) =>
  <FormData
    name={props.name}
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
    definition={[
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
        icon: 'fa fa-fw fa-circle-info',
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
            name: 'registration.tutorRoleName',
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
            name: 'registration.learnerRoleName',
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
            name: 'registration.tutorRoleName',
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
            name: 'registration.learnerRoleName',
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

CourseParameters.propTypes = {
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
  CourseParameters
}
