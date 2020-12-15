import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Tokens} from '#/main/core/tools/parameters/tokens/components/tokens'
import {Token} from '#/main/core/tools/parameters/tokens/components/token'

const TokensTool = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('tokens', {}, 'integration'),
      target: `${props.path}/tokens`
    }]}
    subtitle={trans('tokens', {}, 'integration')}
    actions={[
      {
        name: 'token-add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_token', {}, 'security'),
        target: `${props.path}/tokens/form`,
        primary: true
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/tokens',
          exact: true,
          component: Tokens
        }, {
          path: '/tokens/form/:id?',
          component: Token,
          onEnter: (params) => props.openForm(params.id)
        }
      ]}
    />
  </ToolPage>

TokensTool.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired
}

export {
  TokensTool
}
