import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/plugin/cursus/tools/cursus/catalog/store'

import {CatalogDetails as CatalogDetailsComponent} from '#/plugin/cursus/tools/cursus/catalog/components/details'

const CatalogDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    course: selectors.course(state),
    activeSession: selectors.activeSession(state),
    availableSessions: selectors.availableSessions(state)
  }),
  (dispatch) => ({
    openSession(sessionId) {
      dispatch(actions.openSession(sessionId))
    }
  })
)(CatalogDetailsComponent)

export {
  CatalogDetails
}
