import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {Sessions} from '#/plugin/cursus/administration/cursus/session/components/sessions'
import {SessionForm} from '#/plugin/cursus/administration/cursus/session/components/session-form'
import {actions} from '#/plugin/cursus/administration/cursus/session/store'

const SessionTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('create_session', {}, 'cursus')}
      target="/sessions/form"
      primary={true}
    />
  </PageActions>

const SessionTabComponent = () =>
  <Routes
    routes={[
      {
        path: '/sessions',
        exact: true,
        component: Sessions
      }, {
        path: '/sessions/form/:id?',
        component: SessionForm
        // onEnter: (params) => props.openForm(params.id || null)
      }
    ]}
  />

SessionTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const SessionTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      if (id) {
        dispatch(actions.open('sessions.current', {}, id))
      }
    }
  })
)(SessionTabComponent)

export {
  SessionTabActions,
  SessionTab
}
