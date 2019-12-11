import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/store/selectors'

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
        label: trans('port')
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

const displayFields = {
  'native': [],
  'claro_pdo': [],
  'pdo': [
    'session.db_table',
    'session.db_id_col',
    'session.db_data_col',
    'session.db_data_col',
    'session.db_time_col',
    'session.db_dsn',
    'session.db_user',
    'session.db_password'
  ]
}

const display = (transport, property) => {
  return displayFields[transport].indexOf(property) > -1
}

const Technical = props =>
  <FormData
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    locked={props.lockedParameters}
    sections={[
      {
        icon: 'fa fa-fw fa-internet-explorer',
        title: trans('internet'),
        defaultOpened: true,
        fields: [
          {
            name: 'internet.domain_name',
            type: 'string',
            label: trans('domain_name'),
            linked: [
              {
                name: 'ssl.enabled',
                type: 'boolean',
                label: trans('ssl_enabled')
              }, {
                name: 'ssl.version',
                type: 'string',
                label: trans('version'),
                displayed: (parameters) => parameters.ssl.enabled
              }
            ]
          }, {
            name: 'internet.google_meta_tag',
            type: 'string',
            label: trans('google_tag_validation')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-book',
        title: trans('workspaces'),
        fields: [
          {
            name: 'workspace.max_storage_size',
            label: trans('available_storage'),
            type: 'string'
          }, {
            name: 'workspace.max_upload_resources',
            label: trans('count_resources'),
            type: 'number'
          }, {
            name: 'workspace.max_workspace_users',
            label: trans('workspaces_max_users'),
            type: 'number'
          }
        ]
      }, {
        icon: 'fa fa-fw fa-user-shield',
        title: trans('security'),
        fields: [
          {
            name: 'security.default_root_anon_id',
            type: 'string',
            label: trans('default_admin')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-envelope',
        title: trans('email'),
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
      }, {
        icon: 'fa fa-fw fa-sign-out-alt',
        title: trans('sessions'),
        fields: [
          {
            name: 'security.cookie_lifetime',
            type: 'number',
            label: trans('cookie_lifetime'),
            required: true
          }, {
            name: 'session.storage_type',
            type: 'choice',
            label: trans('storage_type'),
            required: true,
            options: {
              choices: {
                'native': 'native',
                'claro_pdo': 'claro_pdo',
                'pdo': 'pdo'
              }
            },
            linked: [
              {
                name: 'session.db_table',
                type: 'string',
                label: trans('db_table'),
                displayed: parameters => display(parameters.session.storage_type, 'session.db_table')
              }, {
                name: 'session.db_id_col',
                type: 'string',
                label: trans('id_col'),
                displayed: parameters => display(parameters.session.storage_type, 'session.db_id_col')
              }, {
                name: 'session.db_data_col',
                type: 'string',
                label: trans('data_col'),
                displayed: parameters => display(parameters.session.storage_type, 'session.db_data_col')
              }, {
                name: 'session.db_dsn',
                type: 'string',
                label: trans('DSN'),
                displayed: parameters => display(parameters.session.storage_type, 'session.db_dsn')
              }, {
                name: 'session.db_user',
                type: 'string',
                label: trans('user'),
                displayed: parameters => display(parameters.session.storage_type, 'session.db_user')
              }, {
                name: 'session.db_password',
                type: 'string',
                label: trans('password'),
                displayed: parameters => display(parameters.session.storage_type, 'session.db_password')
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-file',
        title: trans('javascripts'),
        fields: [{
          name: 'javascripts',
          label: trans('javascripts'),
          type: 'collection',
          options: {
            type: 'file',
            placeholder: trans('no_javascript'),
            button: trans('add_javascript')
          }
        }]
      }
    ]}
  />

Technical.propTypes = {
  path: T.string.isRequired,
  lockedParameters: T.arrayOf(T.string).isRequired,
  mailer: T.shape({
    transport: T.string
  })
}

export {
  Technical
}
