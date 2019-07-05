import React from 'react'

import {trans} from '#/main/app/intl/translation'
//import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

const Tokens = () =>
  <ListData
    name="api_tokens.tokens"
    fetch={{
      url: ['apiv2_apitoken_list'],
      autoload: true
    }}
    /*primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `/token/form/${row.id}`
    })}*/
    delete={{
      url: ['apiv2_apitoken_delete_bulk']
    }}
    definition={[
      {
        name: 'token',
        label: trans('token', {}, 'claroline'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'description',
        label: trans('description'),
        type: 'html',
        displayed: true
      }, {
        name: 'user.username',
        label: trans('user'),
        type: 'string',
        displayed: true
      }
    ]}
  />

export {
  Tokens
}
