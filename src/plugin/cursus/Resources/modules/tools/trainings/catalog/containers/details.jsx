import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CatalogDetails as CatalogDetailsComponent} from '#/plugin/cursus/tools/trainings/catalog/components/details'

const CatalogDetails = withRouter(connect(
  (state) => ({
    path: toolSelectors.path(state),
    course: selectors.course(state),
    activeSession: selectors.activeSession(state),
    activeSessionRegistration: selectors.activeSessionRegistration(state),
    courseRegistration: selectors.courseRegistration(state),
    availableSessions: selectors.availableSessions(state),
    participantsView: selectors.participantsView(state)
  }),
  (dispatch) => ({
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
