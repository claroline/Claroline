import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

import {CatalogDetails as CatalogDetailsComponent} from '#/plugin/cursus/tools/trainings/catalog/components/details'

const CatalogDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    course: selectors.course(state),
    activeSession: selectors.activeSession(state),
    activeSessionRegistration: selectors.activeSessionRegistration(state),
    availableSessions: selectors.availableSessions(state)
  }),
  (dispatch) => ({
    openSession(sessionId) {
      dispatch(actions.openSession(sessionId))
    },
    register(course, sessionId) {
      dispatch(actions.register(course, sessionId))
    }
  })
)(CatalogDetailsComponent)

export {
  CatalogDetails
}
