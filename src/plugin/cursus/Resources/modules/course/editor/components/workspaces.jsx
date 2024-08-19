import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const CourseEditorWorkspaces = (props) =>
  <EditorPage
    title={trans('workspaces')}
    help={trans('course_workspaces_help', {}, 'cursus')}
    definition={[
      {
        title: trans('workspace'),
        disabled: 'workspace' === props.contextType,
        primary: true,
        fields: [
          {
            name: '_workspaceType',
            type: 'choice',
            label: trans('type'),
            hideLabel: true,
            options: {
              condensed: false,
              choices: {
                none: trans('none'),
                workspace: trans('course_type_workspace', {}, 'cursus'),
                model: trans('course_type_model', {}, 'cursus')
              }
            },
            calculated: (course) => {
              if (get(course, '_workspaceType')) {
                return get(course, '_workspaceType')
              }

              if (get(course, 'workspace', null)) {
                if (get(props.course, 'workspace.meta.model', false)) {
                  return 'model'
                }
                return 'workspace'
              }
              return 'none'
            },
            onChange: () => {
              props.update(props.name, 'workspace', null)
              props.update(props.name, 'registration.tutorRole', null)
              props.update(props.name, 'registration.learnerRole', null)
            },
            linked: [
              {
                name: 'workspace',
                type: 'workspace',
                label: get(props.course, 'workspace.meta.model', false) || 'model' === get(props.course, '_workspaceType') ? trans('workspace_model') : trans('workspace'),
                required: true,
                options: {
                  picker: {
                    model: get(props.course, 'workspace.meta.model', false) || 'model' === get(props.course, '_workspaceType'),
                    title: get(props.course, 'workspace.meta.model', false) || 'model' === get(props.course, '_workspaceType') ? trans('workspace_models', {}, 'workspace') : trans('workspaces')
                  }
                },
                displayed: (course) => get(course, 'workspace', null) || ['workspace', 'model'].includes(get(course, '_workspaceType'))
              }
            ]
          }, {
            name: 'registration.tutorRole',
            type: 'role',
            label: trans('tutor_role', {}, 'cursus'),
            displayed: (course) => get(course, 'workspace', null),
            options: {
              picker: {
                url: ['apiv2_workspace_list_roles', {id: get(props.course, 'workspace.id', null)}],
                filters: []
              }
            },
            help: trans('tutor_role_help', {}, 'cursus')
          }, {
            name: 'registration.learnerRole',
            type: 'role',
            label: trans('learner_role', {}, 'cursus'),
            displayed: (course) => get(course, 'workspace', null),
            options: {
              picker: {
                url: ['apiv2_workspace_list_roles', {id: get(props.course, 'workspace.id', null)}],
                filters: []
              }
            },
            help: trans('learner_role_help', {}, 'cursus')
          }
        ]
      }
    ]}
  />

export {
  CourseEditorWorkspaces
}
