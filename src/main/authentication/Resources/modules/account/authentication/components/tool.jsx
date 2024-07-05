import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {User as UserTypes} from '#/main/community/prop-types'
import {AccountPage} from '#/main/app/account/containers/page'

import {MODAL_TOKEN_PARAMETERS} from '#/main/authentication/token/modals/parameters'
import {TokenList} from '#/main/authentication/token/components/list'
import {selectors} from '#/main/authentication/account/authentication/store'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {Button} from '#/main/app/action'
import changePasswordAction from '#/main/community/actions/user/password-change'
import {ContentTitle} from '#/main/app/content/components/title'
import {Alert} from '#/main/app/components/alert'
import {Tool} from '#/main/core/tool'

const AuthenticationTool = props =>
  <Tool
    {...props}
  >
    <AccountPage
      title={trans('authentication', {}, 'tools')}
    >
      <div className="row">
        <ContentSizing size="md" className="my-4">
          <Button
            className="btn btn-primary"
            {...changePasswordAction([props.currentUser])}
            icon={undefined}
          />
        </ContentSizing>
      </div>

      <div className="row bg-body-tertiary">
        <ContentSizing size="md" className="my-4">
          <ContentTitle
            displayLevel={3}
            title={trans('tokens', {}, 'security')}
          />

          <p className="text-body-secondary">
            {trans('tokens_help', {}, 'security')}
          </p>

          <Alert type="info">
            {trans('tokens_info', {}, 'security')}
          </Alert>

          <Button
            className="btn btn-outline-primary"
            {...{
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
            }}
            icon={undefined}
          />

          <TokenList
            className="mt-3"
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
                disabled: !rows[0] || !hasPermission('edit', rows[0]) || get(rows[0], 'restrictions.locked', false),
                scope: ['object'],
                group: trans('management')
              }
            ]}
          />
        </ContentSizing>
      </div>
    </AccountPage>
  </Tool>

AuthenticationTool.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  invalidateList: T.func.isRequired
}

export {
  AuthenticationTool
}
