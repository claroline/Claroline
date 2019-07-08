import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/technical/store/selectors'

const Limits = () =>
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
        title: trans('pdf'),
        defaultOpened: true,
        fields: [
          {
            name: 'workspace.max_storage_size',
            label: trans('max_storage_size'),
            type: 'string',
            displayed: true
          },
          {
            name: 'workspace.max_upload_resources',
            label: trans('count_resources'),
            type: 'number',
            displayed: true
          },
          {
            name: 'workspace.max_workspace_users',
            label: trans('workspaces_max_users'),
            type: 'number',
            displayed: true
          }
        ]
      }
    ]}
  />

export {
  Limits
}
