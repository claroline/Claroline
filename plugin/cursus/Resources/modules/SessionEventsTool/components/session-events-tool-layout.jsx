import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {selectors} from '../selectors'
import {actions} from '../actions'

import {Router} from '#/main/app/router'

import {ManagerView} from './manager-view.jsx'
import {UserView} from './user-view.jsx'
import {EventView} from './event-view.jsx'

import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'

let SessionEventsToolLayout = props =>
  <div>
    <Router
      routes={[
        {
          path: '/',
          exact: true,
          component: props.canEdit ? ManagerView : UserView
        }, {
          path: '/event/:id',
          component: EventView,
          onEnter: (params = {}) => props.fetch(params.id)
        }
      ]}
    />

    <ModalOverlay />
  </div>

SessionEventsToolLayout.propTypes = {
  canEdit: T.bool,
  fetch: T.func.isRequired
}

SessionEventsToolLayout = connect(
  (state) => ({
    canEdit: selectors.canEdit(state)
  }),
  (dispatch) => ({
    fetch(id) {
      dispatch(actions.fetchSessionEvent(id))
    }
  })
)(SessionEventsToolLayout)

export {
  SessionEventsToolLayout
}
