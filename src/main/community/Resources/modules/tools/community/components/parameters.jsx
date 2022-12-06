import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants as registrationConst} from '#/main/app/security/registration/constants'
import {route} from '#/main/core/workspace/routing'

import {selectors} from '#/main/core/tool/modals/parameters/store'

const workspaceDefinition = (contextData, update) => [
  {
    icon: 'fa fa-fw fa-user-plus',
    title: trans('registration'),
    primary: true,
    fields: [
      {
        name: 'registration.url',
        type: 'url',
        label: trans('registration_url'),
        calculated: () => `${url(['claro_index', {}, true])}#${route(contextData)}`,
        required: true,
        disabled: true
      }, {
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
          picker: contextData ? {
            url: ['apiv2_workspace_list_roles', {id: contextData.id}],
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
      }, {
        name: 'authentication.changePassword',
        type: 'boolean',
        label: trans('allow_change_password', {}, 'community')
      }
    ]
  }, {
    icon: 'fa fa-fw fa-user-plus',
    title: trans('registration'),
    fields: [
      {
        name: 'registration.url',
        type: 'url',
        label: trans('registration_url'),
        calculated: () => `${url(['claro_index', {}, true])}#/registration`,
        required: true,
        disabled: true
      }, {
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
            name: 'registration.auto_logging',
            type: 'boolean',
            label: trans('auto_logging_after_registration'),
            displayed: (parameters) => get(parameters, 'registration.self', false)
          },
          {
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

class CommunityParameters extends Component {
  componentDidMount() {
    if (!this.props.pendingChanges) {
      // this is the only thing I can check to be sure to do this only the first time
      // as this form is mounted through a function in the standard tool parameters form
      // it is re-mounted at each update instead of getting updated
      this.props.load(this.props.parameters)
    }
  }

  render() {
    return (
      <FormData
        embedded={true}
        name={selectors.STORE_NAME}
        dataPart="parameters"
        definition={'desktop' === this.props.contextType ?
          desktopDefinition(this.props.contextData, this.props.updateProp) :
          workspaceDefinition(this.props.contextData, this.props.updateProp)
        }
      />
    )
  }
}

CommunityParameters.propTypes = {
  contextType: T.string.isRequired,
  contextData: T.object,
  parameters: T.object,
  pendingChanges: T.bool.isRequired,
  load: T.func.isRequired,
  updateProp: T.func.isRequired
}

export {
  CommunityParameters
}
