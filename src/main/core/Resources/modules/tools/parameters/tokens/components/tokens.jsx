import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/tools/parameters/store/selectors'

const Tokens = () =>
  <ListData
    name={`${selectors.STORE_NAME}.tokens.list`}
    fetch={{
      url: ['apiv2_apitoken_list_current'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `/tokens/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    delete={{
      url: ['apiv2_apitoken_delete_bulk']
    }}
    definition={[
      {
        name: 'token',
        type: 'string',
        label: trans('token'),
        displayed: true,
        primary: true
      },
      {
        name: 'description',
        type: 'string',
        label: trans('description'),
        displayed: true
      }
    ]}
  />

export {
  Tokens
}
