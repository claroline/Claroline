import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CatalogDetails as CatalogDetailsComponent} from '#/plugin/cursus/tools/trainings/catalog/components/details'

const CatalogDetails = withRouter(connect(
  (state) => ({
    path: toolSelectors.path(state),
    course: selectors.course(state),
    defaultSession: selectors.defaultSession(state),
    activeSession: selectors.activeSession(state),
    registrations: selectors.sessionRegistrations(state),
    availableSessions: selectors.availableSessions(state),
    participantsView: selectors.participantsView(state)
  }),
  (dispatch) => ({
    reload(courseSlug) {
      return dispatch(actions.open(courseSlug, true))
    },
    register(course, sessionId = null, registrationData = null) {
      return dispatch(actions.register(course, sessionId, registrationData))
    },
    openSession(sessionId) {
      dispatch(actions.openSession(sessionId))
    },
    switchParticipantsView(viewMode) {
      dispatch(actions.switchParticipantsView(viewMode))
    }
  })
)(CatalogDetailsComponent))

export {
  CatalogDetails
}
