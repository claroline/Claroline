import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

const CourseForm = (props) =>
  <FormData
    name={props.name}
    buttons={true}
    target={(data, isNew) => isNew ?
      ['apiv2_cursus_course_create'] :
      ['apiv2_cursus_course_update', {id: data.id}]
    }
    cancel={props.cancel}
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
        icon: 'fa fa-fw fa-user-plus',
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
          }
        ]
      }, {
        icon: 'fa fa-fw fa-calendar-week',
        title: trans('sessions', {}, 'cursus'),
        fields: [
          {
            name: 'workspace',
            type: 'workspace',
            label: trans('workspace')
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
            displayed: (course) => !course.workspace
          }, {
            name: 'meta.tutorRoleName',
            type: 'string',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => !course.workspace && !course.workspaceModel
          }, {
            name: 'meta.learnerRoleName',
            type: 'string',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => !course.workspace && !course.workspaceModel
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
          }, {
            name: 'meta.duration',
            type: 'number',
            label: trans('default_session_duration_label', {}, 'cursus'),
            required: true,
            options: {
              min: 0
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
            calculated: (course) => course.restrictions.users || course.restrictions._restrictUsers,
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
                displayed: (course) => course.restrictions.users || course.restrictions._restrictUsers,
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
  name: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  update: T.func.isRequired,
  cancel: T.object
}

export {
  CourseForm
}