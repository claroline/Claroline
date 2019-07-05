import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {Toolbar} from '#/main/app/action/components/toolbar'

import {Tokens} from '#/main/core/administration/integration/apitoken/components/tokens'
import {Token}  from '#/main/core/administration/integration/apitoken/components/token'

const ApiToken = props =>
  <Fragment>
    <Toolbar
      className="page-actions"
      actions={[
        {
          name: 'token-add',
          type: LINK_BUTTON,
          icon: 'fa fa-plus',
          target: '/tokens/form',
          primary: true,
          hideLabel: true
        }
      ]}
    />
    <Routes
      routes={[
        {
          path: '/tokens',
          component: Tokens,
          onEnter: () => {},
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
          component: Token,
          onLeave: () => {
            props.resetForm()
          },
          exact: true
        }
      ]}
    />
  </Fragment>

ApiToken.propTypes = {
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  ApiToken
}
