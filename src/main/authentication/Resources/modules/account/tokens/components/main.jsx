import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {User as UserTypes} from '#/main/community/prop-types'
import {AccountPage} from '#/main/app/account/containers/page'
import {route} from '#/main/app/account/routing'

import {MODAL_TOKEN_PARAMETERS} from '#/main/authentication/token/modals/parameters'
import {TokenList} from '#/main/authentication/token/components/list'
import {selectors} from '#/main/authentication/account/tokens/store'

const TokensMain = props =>
  <AccountPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('tokens', {}, 'security'),
        target: route('tokens')
      }
    ]}
    title={trans('tokens', {}, 'security')}
    actions={[
      {
        name: 'add-token',
        type: MODAL_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_token', {}, 'security'),
        primary: true,
        modal: [MODAL_TOKEN_PARAMETERS, {
          userDisabled: true,
          token: {
            user: props.currentUser
          },
          onSave: () => props.invalidateList()
        }]
      }
    ]}
  >
    <div style={{
      marginTop: 60 // TODO : manage spacing correctly
    }}>
      <TokenList
        name={selectors.STORE_NAME}
        url={['apiv2_apitoken_list_current']}
        actions={(rows) => [
          {
            name: 'edit',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            modal: [MODAL_TOKEN_PARAMETERS, {
              token: rows[0],
              userDisabled: true,
              onSave: () => props.invalidateList()
            }],
            disabled: !hasPermission('edit', rows[0]) || get(rows[0], 'restrictions.locked', false),
            scope: ['object'],
            group: trans('management')
          }
        ]}
      />
    </div>
  </AccountPage>

TokensMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  invalidateList: T.func.isRequired
}

export {
  TokensMain
}
