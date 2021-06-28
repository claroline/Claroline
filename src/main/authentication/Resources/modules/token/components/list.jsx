import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ListData} from '#/main/app/content/list/containers/data'

const TokenList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    delete={{
      url: ['apiv2_apitoken_delete_bulk'],
      disabled: (rows) => -1 === rows.findIndex(row => hasPermission('delete', row) && !get(rows[0], 'restrictions.locked', false))
    }}
    definition={[
      {
        name: 'token',
        label: trans('token', {}, 'security'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'description',
        label: trans('description'),
        type: 'string',
        displayed: true,
        options: {
          long: true
        }
      }, {
        name: 'restrictions.locked',
        alias: 'locked',
        label: trans('locked'),
        type: 'boolean',
        displayed: true
      }
    ].concat(props.definition)}
    actions={props.actions}
  />

TokenList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  definition: T.array,
  actions: T.func
}

TokenList.defaultProps = {
  url: ['apiv2_apitoken_list'],
  definition: []
}

export {
  TokenList
}
