import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {MODAL_TOKEN_FORM} from '#/main/authentication/token/modals/form'
import {TokenList} from '#/main/authentication/token/components/list'
import {selectors} from '#/main/authentication/administration/authentication/store'
import {PageListSection} from '#/main/app/page'

const AuthenticationTokens = props =>
  <ToolPage
    title={trans('tokens', {}, 'security')}
  >
    <PageListSection
      title={trans('tokens', {}, 'security')}
      addAction={{
        name: 'add-token',
        type: MODAL_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_token', {}, 'security'),
        primary: true,
        modal: [MODAL_TOKEN_FORM, {
          onSave: props.invalidateList
        }]
      }}
    >
      <TokenList
        flush={true}
        className="mb-5"
        name={selectors.STORE_NAME+'.tokens'}
        definition={[
          {
            name: 'user',
            label: trans('user'),
            type: 'user',
            displayed: true,
            order: 0
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

AuthenticationTokens.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired
}

export {
  AuthenticationTokens
}
