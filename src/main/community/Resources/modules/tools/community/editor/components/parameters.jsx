import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {selectors as toolSelectors} from '#/main/core/tool'
import {actions as formActions} from '#/main/app/content/form'
import {ToolEditorOverview} from '#/main/core/tool/editor'

import {constants as registrationConst} from '#/main/app/security/registration/constants'

const workspaceDefinition = (contextId, update) => [
  {
    icon: 'fa fa-fw fa-user-plus',
    title: trans('registration'),
    primary: true,
    fields: [
      {
        name: 'parameters.registration.selfRegistration',
        type: 'boolean',
        label: trans('activate_self_registration'),
        help: trans('self_registration_workspace_help'),
        linked: [
          {
            name: 'parameters.registration.validation',
            type: 'boolean',
            label: trans('validate_registration'),
            help: trans('validate_registration_help'),
            displayed: (parameters) => get(parameters, 'parameters.registration.selfRegistration', false)
          }
        ]
      }, {
        name: 'parameters.registration.selfUnregistration',
        type: 'boolean',
        label: trans('activate_self_unregistration'),
        help: trans('self_unregistration_workspace_help')
      }, {
        name: 'parameters.registration.defaultRole',
        type: 'role',
        label: trans('default_role'),
        options: {
          multiple: false,
          picker: contextId ? {
            url: ['apiv2_workspace_list_roles', {id: contextId}],
            filters: []
          } : undefined
        }
      }, {
        name: 'parameters.registration._restrictMaxTeams',
        type: 'boolean',
        label: trans('restrict_max_teams', {}, 'community'),
        calculated: (parameters) => get(parameters, 'parameters.registration._restrictMaxTeams') || get(parameters, 'parameters.registration.maxTeams'),
        onChange: (enabled) => {
          if (!enabled) {
            update('registration.maxTeams', null)
          }
        },
        linked: [
          {
            name: 'parameters.registration.maxTeams',
            type: 'number',
            label: trans('teams_count', {}, 'community'),
            displayed: (parameters) => get(parameters, 'parameters.registration._restrictMaxTeams') || get(parameters, 'parameters.registration.maxTeams'),
            options: {min: 0}
          }
        ]
      }
    ]
  }
]

const desktopDefinition = () => [
  {
    icon: 'fa fa-fw fa-user-plus',
    title: trans('registration'),
    primary: true,
    fields: [
      {
        name: 'parameters.registration.self',
        type: 'boolean',
        label: trans('activate_self_registration'),
        help: trans('self_registration_platform_help'),
      }, {
        name: 'parameters.registration.default_role',
        type: 'role',
        label: trans('default_role'),
        required: true
      }, {
        name: 'parameters.registration.validation',
        type: 'choice',
        label: trans('registration_mail_validation'),
        required: true,
        options: {
          noEmpty: true,
          condensed: false,
          choices: registrationConst.registrationValidationTypes
        }
      }
    ]
  }, {
    id: 'profile',
    icon: 'fa fa-fw fa-address-card',
    title: trans('user_profile'),
    primary: true,
    fields: [
      {
        name: 'parameters.community.username',
        type: 'boolean',
        label: trans('enable_username', {}, 'community'),
        help: [
          trans('username_enabled_help', {}, 'community'),
          trans('username_disabled_help', {}, 'community')
        ]
      }, {
        name: 'parameters.profile.roles_edition',
        type: 'role',
        label: trans('profile_roles_for_edition'),
        options: {multiple: true}
      }, {
        name: 'parameters.profile.roles_confidential',
        type: 'role',
        label: trans('profile_roles_for_confidential_fields'),
        options: {multiple: true}
      }, {
        name: 'parameters.profile.roles_locked',
        type: 'role',
        label: trans('profile_roles_for_locked_fields'),
        options: {multiple: true}
      }, {
        name: 'parameters.profile.show_email',
        type: 'role',
        label: trans('show_email'),
        options: {multiple: true}
      }
    ]
  }
]

const CommunityEditorParameters = () => {
  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)

  const dispatch = useDispatch()
  const updateProp = (prop, value) => {
    dispatch(formActions.updateProp(toolSelectors.EDITOR_NAME, 'parameters.'+prop, value))
  }

  return (
    <ToolEditorOverview
      definition={'desktop' === contextType ?
        desktopDefinition(contextId, updateProp) :
        workspaceDefinition(contextId, updateProp)
      }
    />
  )
}

export {
  CommunityEditorParameters
}
