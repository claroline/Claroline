import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/technical/store/selectors'

const displayFields = {
  'native': [],
  'claro_pdo': [],
  'pdo': ['session.db_table', 'session.db_id_col', 'session.db_data_col', 'session.db_data_col', 'session.db_time_col', 'session.db_dsn', 'session.db_user', 'session.db_password']
}

const display = (transport, property) => {
  return displayFields[transport].indexOf(property) > -1
}

const Sessions = () =>
  <FormData
    name={selectors.FORM_NAME}
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
            name: 'security.cookie_lifetime',
            type: 'number',
            label: trans('cookie_lifetime'),
            required: true,
            options: {
              choices: {
                'native': 'native',
                'claro_pdo': 'claro_pdo',
                'pdo': 'pdo'
              }
            }
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
            }
          }, {
            name: 'session.db_table',
            type: 'string',
            label: trans('db_table'),
            required: false,
            displayed: parameters => display(parameters.session.storage_type, 'session.db_table')
          }, {
            name: 'session.db_id_col',
            type: 'string',
            label: trans('id_col'),
            required: false,
            displayed: parameters => display(parameters.session.storage_type, 'session.db_id_col')
          }, {
            name: 'session.db_data_col',
            type: 'string',
            label: trans('data_col'),
            required: false,
            displayed: parameters => display(parameters.session.storage_type, 'session.db_data_col')
          }, {
            name: 'session.db_dsn',
            type: 'string',
            label: trans('DSN'),
            required: false,
            displayed: parameters => display(parameters.session.storage_type, 'session.db_dsn')
          }, {
            name: 'session.db_user',
            type: 'string',
            label: trans('user'),
            required: false,
            displayed: parameters => display(parameters.session.storage_type, 'session.db_user')
          }, {
            name: 'session.db_password',
            type: 'string',
            label: trans('password'),
            required: false,
            displayed: parameters => display(parameters.session.storage_type, 'session.db_password')
          }
        ]
      }
    ]}
  />

export {
  Sessions
}
