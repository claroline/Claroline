import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {actions} from '#/main/core/tools/parameters/token/store/actions'
import {Routes} from '#/main/app/router'

import {Tokens} from '#/main/core/tools/parameters/token/components/tokens'
import {Token} from '#/main/core/tools/parameters/token/components/token'

import {CALLBACK_BUTTON} from '#/main/app/buttons'

const TokenTab = props =>
  <PageActions>
    <PageAction
      type={CALLBACK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_token')}
      callback= {props.create}
      primary={true}
    />
  </PageActions>

const TokenTabActions = connect(
  null,
  dispatch => ({
    create() {
      dispatch(actions.create())
    }
  })
)(TokenTab)

const TokenTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/tokens',
        exact: true,
        component: Tokens
      }, {
        path: '/tokens/:id?',
        component: Token,
        onEnter: (params) => props.openForm(params.id),
        onLeave: props.closeForm
      }
    ]}
  />

const ConnectedComponent = connect(
  null,
  dispatch => ({
    openForm(id) {
      dispatch(actions.open('tokens.current', id))
    }
  })
)(TokenTabComponent)

export {
  TokenTabActions,
  ConnectedComponent as TokenTabComponent
}
