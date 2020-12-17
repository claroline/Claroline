import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Tokens} from '#/main/authentication/integration/tokens/components/tokens'
import {Token}  from '#/main/authentication/integration/tokens/components/token'

const ApiToken = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('tokens', {}, 'integration'),
      target: `${props.path}/tokens`
    }]}
    subtitle={trans('tokens', {}, 'integration')}
    primaryAction="add-token"
    actions={[
      {
        name: 'add-token',
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
          component: Tokens,
          exact: true
        }/*, {
          path: '/token/form/:id?',
          component: Token,
          onEnter: (params) => {
            props.openForm(params.id || null)
          },
          onLeave: () => {
            props.resetForm()
          }
        }*/, {
          path: '/tokens/form',
          render: () => {
            const component = <Token path={props.path} />

            return component
          },
          onLeave: () => {
            props.resetForm()
          },
          exact: true
        }
      ]}
    />
  </ToolPage>

ApiToken.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  ApiToken
}
