import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {constants as registrationConst} from '#/main/app/security/registration/constants'

const Parameters = (props) => {
  const roleEnum = {}
  props.platformRoles.forEach(role => {
    roleEnum[role.name] = trans(role.translationKey)
  })

  return (
    <ToolPage
      path={[{
        type: LINK_BUTTON,
        label: trans('parameters'),
        target: `${props.path}/parameters`
      }]}
      subtitle={trans('parameters')}
    >
      <FormData
        level={3}
        name={`${baseSelectors.STORE_NAME}.parameters`}
        target={['apiv2_parameters_update']}
        buttons={true}
        sections={[
          {
            icon: 'fa fa-fw fa-user-plus',
            title: trans('registration'),
            primary: true,
            fields: [
              // todo auto_logging
              // todo self unregistration
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
                    displayed: props.parameters.registration && props.parameters.registration.self
                  }, {
                    name: 'registration.auto_logging',
                    type: 'boolean',
                    label: trans('auto_logging_after_registration'),
                    displayed: props.parameters.registration && props.parameters.registration.self
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
                    displayed: props.parameters.registration && props.parameters.registration.self
                  }
                ]
              }, {
                name: 'registration.default_role',
                type: 'choice',
                label: trans('default_role'),
                required: true,
                options: {
                  choices: roleEnum,
                  condensed: true,
                  noEmpty: true
                }
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
              }, {
                name: 'registration.selfUnregistration',
                type: 'boolean',
                label: trans('activate_self_unregistration'),
                help: trans('self_unregistration_platform_help')
              }
            ]
          }, {
            icon: 'fa fa-fw fa-sign-in',
            title: trans('login'),
            fields: [
              {
                name: 'authentication.help',
                type: 'html',
                label: trans('message')
              }, {
                name: 'authentication.redirect_after_login_option',
                type: 'choice',
                label: trans('redirect_after_login_option'),
                options: {
                  multiple: false,
                  condensed: false,
                  choices: {
                    'DESKTOP': 'DESKTOP',
                    'URL': 'URL',
                    'WORKSPACE_TAG': 'WORKSPACE_TAG',
                    'LAST': 'LAST'
                  }
                }, linked: [{
                  name: 'authentication.redirect_after_login_url',
                  type: 'string',
                  label: trans('redirect_after_login_url'),
                  displayed: (data) => data.authentication.redirect_after_login_option === 'URL',
                  hideLabel: true
                }, {
                  name: 'workspace.default_tag',
                  label: trans('default_workspace_tag'),
                  type: 'string',
                  displayed: (data) => data.authentication.redirect_after_login_option === 'WORKSPACE_TAG',
                  hideLabel: true
                }]
              }, {
                name: 'registration.auto_logging',
                type: 'boolean',
                label: trans('auto_logging_after_registration'),
                displayed: false // FIXME
              }, {
                name: 'authentication.changePassword',
                type: 'boolean',
                label: trans('allow_change_password')
              }
            ]
          }, {
            id: 'profile',
            icon: 'fa fa-fw fa-address-card',
            title: trans('user_profile'),
            fields: [
              {
                name: 'profile.roles_edition',
                type: 'choice',
                label: trans('profile_roles_for_edition'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: Object.keys(roleEnum).filter(r => ['ROLE_ADMIN', 'ROLE_ANONYMOUS'].indexOf(r) === -1).reduce((choices, key) => {
                    choices[key] = roleEnum[key]

                    return choices
                  }, {})
                }
              }, {
                name: 'profile.roles_confidential',
                type: 'choice',
                label: trans('profile_roles_for_confidential_fields'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: Object.keys(roleEnum).filter(r => r !== 'ROLE_ADMIN').reduce((choices, key) => {
                    choices[key] = roleEnum[key]

                    return choices
                  }, {})
                }
              }, {
                name: 'profile.roles_locked',
                type: 'choice',
                label: trans('profile_roles_for_locked_fields'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: Object.keys(roleEnum).filter(r => ['ROLE_ADMIN', 'ROLE_ANONYMOUS'].indexOf(r) === -1).reduce((choices, key) => {
                    choices[key] = roleEnum[key]

                    return choices
                  }, {})
                }
              }, {
                name: 'profile.show_email',
                type: 'choice',
                label: trans('show_email'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: Object.keys(roleEnum).filter(r => ['ROLE_ADMIN', 'ROLE_ANONYMOUS'].indexOf(r) === -1).reduce((choices, key) => {
                    choices[key] = roleEnum[key]

                    return choices
                  }, {})
                }
              }
            ]
          }
        ]}
      />
    </ToolPage>
  )
}

Parameters.propTypes = {
  path: T.string.isRequired,
  platformRoles: T.array.isRequired,
  parameters: T.shape({
    registration: T.shape({
      self: T.bool
    })
  }).isRequired
}

const ParametersTab = connect(
  (state) => ({
    path: toolSelectors.path(state),
    parameters: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.parameters')),
    platformRoles: baseSelectors.platformRoles(state)
  })
)(Parameters)

export {
  ParametersTab
}
