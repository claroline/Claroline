import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {constants, constants as registrationConst} from '#/main/app/security/registration/constants'
import {ToolEditorOverview} from '#/main/core/tool/editor/components/overview'

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

const desktopDefinition = (contextId, update) => [
  {
    title: trans('general'),
    primary: true,
    hideTitle: true,
    fields: [
      {
        name: 'parameters.community.username',
        type: 'boolean',
        label: trans('enable_username', {}, 'community'),
        help: [
          trans('username_enabled_help', {}, 'community'),
          trans('username_disabled_help', {}, 'community')
        ]
      }
    ]
  }, {
    icon: 'fa fa-fw fa-user-plus',
    title: trans('registration'),
    primary: true,
    fields: [
      {
        name: 'parameters.registration.self',
        type: 'boolean',
        label: trans('activate_self_registration'),
        help: trans('self_registration_platform_help'),
        linked: [
          {
            name: 'parameters.registration.organization_selection',
            type: 'boolean',
            label: trans('allow_organization_selection'),
            calculated: (parameters) => {
              console.log(get(parameters, 'parameters.registration.organization_selection'))
              return constants.ORGANIZATION_SELECTION_SELECT === get(parameters, 'parameters.registration.organization_selection')
            },
            displayed: (parameters) => get(parameters, 'parameters.registration.self', false),
            onChange: (enabled) => {
              update('registration.organization_selection', !enabled ? null : constants.ORGANIZATION_SELECTION_SELECT)
            }
          }
        ]
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
        name: 'parameters.profile.roles_edition',
        type: 'roles',
        label: trans('profile_roles_for_edition')
      }, {
        name: 'parameters.profile.roles_confidential',
        type: 'roles',
        label: trans('profile_roles_for_confidential_fields')
      }, {
        name: 'parameters.profile.roles_locked',
        type: 'roles',
        label: trans('profile_roles_for_locked_fields')
      }, {
        name: 'parameters.profile.show_email',
        type: 'roles',
        label: trans('show_email')
      }
    ]
  }
]

const EditorParameters = (props) => {
  useEffect(() => {
    if (props.loaded) {
      // load tool parameters inside the form
      props.load(props.parameters)
    }
  }, [props.contextType, props.contextId, props.loaded])

  return (
    <ToolEditorOverview
      disabled={!props.loaded}
      definition={'desktop' === props.contextType ?
        desktopDefinition(props.contextId, props.updateProp) :
        workspaceDefinition(props.contextId, props.updateProp)
      }
    />
  )
}

EditorParameters.propTypes = {
  loaded: T.bool.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  parameters: T.object,
  load: T.func.isRequired,
  updateProp: T.func.isRequired
}

export {
  EditorParameters
}
