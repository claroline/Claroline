import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {selectors} from '#/main/core/tool/editor/store'
import {FormContent} from '#/main/app/content/form/containers/content'
import {trans} from '#/main/app/intl'
import get from 'lodash/get'
import {constants as registrationConst} from '#/main/app/security/registration/constants'

const workspaceDefinition = (contextId, update) => [
  {
    icon: 'fa fa-fw fa-user-plus',
    title: trans('registration'),
    primary: true,
    fields: [
      {
        name: 'registration.selfRegistration',
        type: 'boolean',
        label: trans('activate_self_registration'),
        help: trans('self_registration_workspace_help'),
        linked: [
          {
            name: 'registration.validation',
            type: 'boolean',
            label: trans('validate_registration'),
            help: trans('validate_registration_help'),
            displayed: (parameters) => get(parameters, 'registration.selfRegistration', false)
          }
        ]
      }, {
        name: 'registration.selfUnregistration',
        type: 'boolean',
        label: trans('activate_self_unregistration'),
        help: trans('self_unregistration_workspace_help')
      }, {
        name: 'registration.defaultRole',
        type: 'role',
        label: trans('default_role'),
        options: {
          picker: contextId ? {
            url: ['apiv2_workspace_list_roles', {id: contextId}],
            filters: []
          } : undefined
        }
      }, {
        name: 'registration._restrictMaxTeams',
        type: 'boolean',
        label: trans('restrict_max_teams', {}, 'community'),
        calculated: (parameters) => get(parameters, 'registration._restrictMaxTeams') || get(parameters, 'registration.maxTeams'),
        onChange: (enabled) => {
          if (!enabled) {
            update('registration.maxTeams', null)
          }
        },
        linked: [
          {
            name: 'registration.maxTeams',
            type: 'number',
            label: trans('teams_count', {}, 'community'),
            displayed: (parameters) => get(parameters, 'registration._restrictMaxTeams') || get(parameters, 'registration.maxTeams'),
            options: {min: 0}
          }
        ]
      }
    ]
  }
]

const desktopDefinition = () => [
  {
    title: trans('general'),
    primary: true,
    fields: [
      {
        name: 'community.username',
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
    fields: [
      {
        name: 'registration.self',
        type: 'boolean',
        label: trans('activate_self_registration'),
        help: trans('self_registration_platform_help'),
        linked: [
          {
            name: 'registration.allow_workspace',
            type: 'boolean',
            label: trans('allow_workspace_registration'),
            displayed: (parameters) => get(parameters, 'registration.self', false)
          }, {
            name: 'registration.organization_selection',
            type: 'choice',
            label: trans('organizations'),
            options: {
              multiple: false,
              condensed: false,
              choices: registrationConst.ORGANIZATION_SELECTION_CHOICES
            },
            displayed: (parameters) => get(parameters, 'registration.self', false)
          }
        ]
      }, {
        name: 'registration.default_role',
        type: 'role',
        label: trans('default_role'),
        required: true
      }, {
        name: 'registration.validation',
        type: 'choice',
        label: trans('registration_mail_validation'),
        required: true,
        options: {
          noEmpty: true,
          condensed: true,
          choices: registrationConst.registrationValidationTypes
        }
      }, { // TODO : implement
        name: 'registration.selfUnregistration',
        type: 'boolean',
        label: trans('activate_self_unregistration'),
        help: trans('self_unregistration_platform_help'),
        displayed: false
      }
    ]
  }, {
    id: 'profile',
    icon: 'fa fa-fw fa-address-card',
    title: trans('user_profile'),
    fields: [
      {
        name: 'profile.roles_edition',
        type: 'roles',
        label: trans('profile_roles_for_edition')
      }, {
        name: 'profile.roles_confidential',
        type: 'roles',
        label: trans('profile_roles_for_confidential_fields')
      }, {
        name: 'profile.roles_locked',
        type: 'roles',
        label: trans('profile_roles_for_locked_fields')
      }, {
        name: 'profile.show_email',
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
    <FormContent
      disabled={!props.loaded}
      name={selectors.STORE_NAME}
      dataPart="parameters"
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
