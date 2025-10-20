import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/authentication/administration/authentication/store'
import {PageContent} from '#/main/app/page'
import {AuthenticationOauth} from '#/main/authentication/administration/authentication/components/oauth'

const displayPasswordValidation = (data) => get(data, 'password._forceComplexity')
  || get(data, 'password.minLength')
  || get(data, 'password.requireLowercase')
  || get(data, 'password.requireUppercase')
  || get(data, 'password.requireNumber')
  || get(data, 'password.requireSpecialChar')

const AuthenticationParameters = (props) =>
  <ToolPage title={trans('parameters')}>
    <PageContent className="pt-5 d-flex flex-column">
      <FormData
        className="flex-fill mb-5"
        name={selectors.FORM_NAME}
        target={['apiv2_authentication_parameters_update']}
        buttons={true}
        cancel={{
          type: LINK_BUTTON,
          target: props.path,
          exact: true
        }}
        definition={[
          {
            title: trans('Authentification par mot de passe'),
            description: trans('Autorisez les utilisateurs à se connecter à votre plateforme avec les identifiants (nom d\'utilisateur ou email) et mots de passe définis pour leur compte.'),
            primary: true,
            hideTitle: false,
            enabled: true,
            onToggle: () => {

            },
            fields: [
              /*{
                name: 'login.helpMessage',
                type: 'html',
                label: trans('message')
              }, */{
                name: 'login.internalAccount',
                type: 'boolean',
                label: trans('display_on_login_page', {}, 'security'),
                help: trans('Affichez le formulaire de connexion (identifiant et mot de passe) sur la page de connexion de la plateforme.')
              }, {
                name: 'login.changePassword',
                type: 'boolean',
                label: trans('allow_change_password', {}, 'security'),
                help: trans('Autorisez les utilisateurs à modifier leur mot de passe depuis les paramètres de leur compte.', {}, 'security')
              }, {
                name: 'password._forceComplexity',
                type: 'boolean',
                label: trans('force_password_complexity', {}, 'security'),
                help: trans('Définissez des règles de validation pour la création de nouveaux mots de passe.', {}, 'security'),
                calculated: displayPasswordValidation,
                onChange: (value) => {
                  if (!value) {
                    props.update('password.minLength', null)
                    props.update('password.requireLowercase', false)
                    props.update('password.requireUppercase', false)
                    props.update('password.requireNumber', false)
                    props.update('password.requireSpecialChar', false)
                  }
                },
                linked: [
                  {
                    name: 'password.minLength',
                    type: 'number',
                    label: trans('minLength', {}, 'security'),
                    displayed: displayPasswordValidation
                  }, {
                    name: 'password.requireLowercase',
                    type: 'boolean',
                    label: trans('requireLowercase', {}, 'security'),
                    displayed: displayPasswordValidation
                  }, {
                    name: 'password.requireUppercase',
                    type: 'boolean',
                    label: trans('requireUppercase', {}, 'security'),
                    displayed: displayPasswordValidation
                  }, {
                    name: 'password.requireNumber',
                    type: 'boolean',
                    label: trans('requireNumber', {}, 'security'),
                    displayed: displayPasswordValidation
                  }, {
                    name: 'password.requireSpecialChar',
                    type: 'boolean',
                    label: trans('requireSpecialChar', {}, 'security'),
                    displayed: displayPasswordValidation
                  }
                ]
              }
            ]
          }, {
            title: trans('Authentification par application externe'),
            description: trans('Autorisez les utilisateurs à se connecter à votre plateforme en utilisant un service d\'authentification externe.'),
            primary: true,
            hideTitle: false,
            component: AuthenticationOauth
          }, {
            title: trans('Authentification par Jetons'),
            description: trans('Générez des jetons d\'authentification.'),
            primary: true,
            hideTitle: false,
            enabled: true,
            displayed: false,
            onToggle: () => {

            },
            render: () => {
              return 'placeholder'
            }
          }, {
            title: trans('Authentification par IPs'),
            description: trans('Authentifiez automatiquement les utilisateurs lorsqu\'ils accèdent à la plateforme depuis l\'une des ips autorisées.'),
            primary: true,
            hideTitle: false,
            enabled: true,
            displayed: false,
            onToggle: () => {

            },
            render: () => {
              return 'placeholder'
            }
          }
        ]}
      />
    </PageContent>
  </ToolPage>

AuthenticationParameters.propTypes = {
  path: T.string.isRequired,
  update: T.func.isRequired
}

export {
  AuthenticationParameters
}
