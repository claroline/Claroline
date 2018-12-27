import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const displayFields = {
  'sendmail': [],
  'gmail': ['mailer.username', 'mailer.password'],
  'smtp': ['mailer.host', 'mailer.username', 'mailer.password', 'mailer.auth_mode', 'mailer.encryption', 'mailer.port'],
  'postal': ['mailer.host', 'mailer.api_key', 'mailer.tag'
  ]
}

const display = (transport, property) => {
  return displayFields[transport].indexOf(property) > -1
}

const Mailing = () =>
  <FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('main'),
        defaultOpened: true,
        fields: [
          {
            name: 'mailer.transport',
            type: 'choice',
            label: trans('transport'),
            required: true,
            options: {
              choices: {
                sendmail: 'sendmail',
                gmail: 'gmail',
                smtp: 'smtp',
                postal: 'postal'
              }
            }
          }, {
            name: 'mailer.host',
            type: 'string',
            label: trans('host'),
            required: false,
            displayed: parameters => display(parameters.mailer.transport, 'mailer.host')
          }, {
            name: 'mailer.port',
            type: 'string',
            label: trans('port'),
            required: false,
            displayed: parameters => display(parameters.mailer.transport, 'mailer.port')
          }, {
            name: 'mailer.username',
            type: 'string',
            label: trans('username'),
            required: false,
            displayed: parameters => display(parameters.mailer.transport, 'mailer.username')
          }, {
            name: 'mailer.password',
            type: 'password',
            label: trans('password'),
            required: false,
            displayed: parameters => display(parameters.mailer.transport, 'mailer.password')
          }, {
            name: 'mailer.auth_mode',
            type: 'choice',
            label: trans('auth_mode'),
            required: false,
            options: {
              choices: {
                'none': 'none',
                'plain': 'plain',
                'login': 'login',
                'cram-md5': 'cram-md5'
              }
            },
            displayed: parameters => display(parameters.mailer.transport, 'mailer.auth_mode')
          }, {
            name: 'mailer.encryption',
            type: 'choice',
            label: trans('encryption'),
            required: false,
            options: {
              choices: {
                'none': 'none',
                'tls': 'tls',
                'ssl': 'ssl'
              }
            },
            displayed: parameters => display(parameters.mailer.transport, 'mailer.encryption')
          }, {
            name: 'mailer.tag',
            type: 'string',
            label: trans('tag'),
            required: false,
            displayed: parameters => display(parameters.mailer.transport, 'mailer.tag')
          }
        ]
      }
    ]}
  />

export {
  Mailing
}
