import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_TOKEN_PARAMETERS} from '#/main/authentication/token/modals/parameters'
import {TokenList} from '#/main/authentication/token/components/list'
import {selectors} from '#/main/authentication/integration/tokens/store'

const ApiToken = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('tokens', {}, 'integration'),
      target: `${props.path}/tokens`
    }]}
    subtitle={trans('tokens', {}, 'integration')}
    /*primaryAction="add-token"*/
    primaryAction={
      {
        name: 'add-token',
        type: MODAL_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_token', {}, 'security'),
        primary: true,
        modal: [MODAL_TOKEN_PARAMETERS, {
          onSave: () => props.invalidateList()
        }]
      }
    }
  >
    <TokenList
      name={selectors.STORE_NAME}
      definition={[
        {
          name: 'user',
          label: trans('user'),
          type: 'user',
          displayed: true,
          order: 0
        }
      ]}
      actions={(rows) => [
        {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_TOKEN_PARAMETERS, {
            token: rows[0],
            onSave: () => props.invalidateList()
          }],
          disabled: !hasPermission('edit', rows[0]) || get(rows[0], 'restrictions.locked', false),
          scope: ['object'],
          group: trans('management')
        }
      ]}
    />
  </ToolPage>

ApiToken.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired
}

export {
  ApiToken
}
