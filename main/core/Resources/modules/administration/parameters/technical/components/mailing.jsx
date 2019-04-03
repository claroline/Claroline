import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const mailers = [
  {
    name: 'sendmail',
    label: trans('mailer_sendmail'),
    fields: []
  }, {
    name: 'gmail',
    label: trans('mailer_gmail'),
    fields: [
      {
        name: 'mailer.username',
        type: 'string',
        label: trans('username')
      }, {
        name: 'mailer.password',
        type: 'password',
        label: trans('password')
      }
    ]
  }, {
    name: 'smtp',
    label: trans('mailer_smtp'),
    fields: [
      {
        name: 'mailer.host',
        type: 'url',
        label: trans('host')
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
        }
      }, {
        name: 'mailer.port',
        type: 'string',
        label: trans('port'),
        required: false
      }, {
        name: 'mailer.auth_mode',
        type: 'choice',
        label: trans('auth_mode'),
        options: {
          condensed: true,
          choices: {
            'none': 'none',
            'plain': 'plain',
            'login': 'login',
            'cram-md5': 'cram-md5'
          }
        },
        linked: [
          {
            name: 'mailer.username',
            type: 'string',
            label: trans('username'),
            displayed: (parameters) => 'none' !== parameters.mailer.auth_mode
          }, {
            name: 'mailer.password',
            type: 'password',
            label: trans('password'),
            displayed: (parameters) => 'none' !== parameters.mailer.auth_mode
          }
        ]
      }
    ]
  }, {
    name: 'postal',
    label: trans('mailer_postal'),
    fields: [
      {
        name: 'mailer.host',
        type: 'url',
        label: trans('host')
      }, {
        name: 'mailer.api_key',
        type: 'string',
        label: trans('api_key')
      }, {
        name: 'mailer.tag',
        type: 'string',
        label: trans('tag'),
        required: false
      }
    ]
  }
]

const MailingForm = (props) =>
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
              condensed: true,
              choices: mailers.reduce((choices, mailer) => Object.assign(choices, {
                [mailer.name]: mailer.label
              }), {})
            },
            linked: props.mailer ? mailers.find(mailer => mailer.name === props.mailer.transport).fields: []
          }
        ]
      }
    ]}
  />

MailingForm.propTypes = {
  mailer: T.shape({
    transport: T.string
  })
}

const Mailing = connect(
  (state) => ({
    mailer: formSelectors.data(formSelectors.form(state, 'parameters')).mailer
  })
)(MailingForm)

export {
  Mailing
}
