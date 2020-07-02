import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {actions} from '#/plugin/cursus/tools/cursus/catalog/session/store'
import {Sessions} from '#/plugin/cursus/tools/cursus/catalog/session/components/sessions'
import {Session} from '#/plugin/cursus/tools/cursus/catalog/session/components/session'

const SessionsCatalogComponent = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/catalog', exact: true, to: '/catalog/sessions'}
    ]}
    routes={[
      {
        path: '/catalog/sessions',
        exact: true,
        render: () => {
          const SessionList = (
            <Sessions path={props.path} />
          )

          return SessionList
        }
      }, {
        path: '/catalog/sessions/:id?',
        render: () => {
          const SessionDetails = (
            <Session path={props.path} />
          )

          return SessionDetails
        },
        onEnter: (params) => props.openSession(params.id),
        onLeave: () => props.resetSession()
      }
    ]}
  />

SessionsCatalogComponent.propTypes = {
  path: T.string.isRequired,
  openSession: T.func.isRequired,
  resetSession: T.func.isRequired
}

const SessionsCatalog = connect(
  null,
  (dispatch) => ({
    openSession(id) {
      dispatch(actions.fetchSession(id))
    },
    resetSession() {
      dispatch(actions.loadSession(null))
    }
  })
)(SessionsCatalogComponent)

export {
  SessionsCatalog
}
