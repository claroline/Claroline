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
import {constants} from '#/main/core/administration/community/parameters/constants'

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
                calculated: () => url(['claro_user_registration', {}, true]),
                required: true,
                disabled: true
              }, {
                name: 'registration.self',
                type: 'boolean',
                label: trans('activate_self_registration'),
                help: trans('self_registration_platform_help'),
                linked: [
                  {
                    name: 'registration.register_button_at_login',
                    type: 'boolean',
                    label: trans('show_register_button_in_login_page'),
                    displayed: props.parameters.registration && props.parameters.registration.self
                  }, {
                    name: 'registration.force_organization_creation',
                    type: 'boolean',
                    label: trans('force_organization_creation'),
                    displayed: props.parameters.registration && props.parameters.registration.self
                  }, {
                    name: 'registration.allow_workspace',
                    type: 'boolean',
                    label: trans('allow_workspace_registration'),
                    displayed: props.parameters.registration && props.parameters.registration.self
                  }
                ]
              }, {
                name: 'registration.default_role',
                type: 'choice',
                label: trans('default_role'),
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: roleEnum
                }
              }, {
                name: 'registration.validation',
                type: 'choice',
                label: trans('registration_mail_validation'),
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.registrationValidationTypes
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-sign-in',
            title: trans('login'),
            fields: [
              {
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
          }, {
            id: 'anonymous',
            icon: 'fa fa-fw fa-user-secret',
            title: trans('anonymous_users'),
            displayed: false, // FIXME
            fields: [
              {
                name: 'security.form_captcha',
                type: 'boolean',
                label: trans('display_captcha')
              }, {
                name: 'security.anonymous_public_profile',
                type: 'boolean',
                label: trans('show_profile_for_anonymous')
              }
            ]
          }, {
            icon: 'fa fa-fw fa-copyright',
            title: trans('term_of_service'),
            fields: [
              {
                name: 'tos.enabled',
                type: 'boolean',
                label: trans('term_of_service_activation_message'),
                help: trans('term_of_service_activation_help'),
                linked: [
                  {
                    name: 'tos.text',
                    type: 'translated',
                    label: trans('term_of_service'),
                    required: true,
                    displayed: props.parameters.tos.enabled
                  }
                ]
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
    }),
    tos: T.shape({
      enabled: T.bool.isRequired
    }).isRequired
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
