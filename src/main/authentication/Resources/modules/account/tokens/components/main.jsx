import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {DOWNLOAD_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {showBreadcrumb} from '#/main/app/layout/utils'
import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {MODAL_TOKEN_PARAMETERS} from '#/main/authentication/token/modals/parameters'
import {TokenList} from '#/main/authentication/token/components/list'
import {selectors} from '#/main/authentication/account/tokens/store'

const TokensMain = props =>
  <UserPage
    showBreadcrumb={showBreadcrumb()}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('my_account'),
        target: '/account'
      }, {
        type: LINK_BUTTON,
        label: trans('tokens', {}, 'security'),
        target: '/account/tokens'
      }
    ]}
    title={trans('tokens', {}, 'security')}
    user={props.currentUser}
    toolbar="add-token | fullscreen more"
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
      }, {
        name: 'export-csv',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-csv', {}, 'actions'),
        file: {
          url: url(['apiv2_apitoken_csv'], {filters: {user: props.currentUser.id}})
        },
        group: trans('transfer')
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
  </UserPage>

TokensMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  invalidateList: T.func.isRequired
}

export {
  TokensMain
}
