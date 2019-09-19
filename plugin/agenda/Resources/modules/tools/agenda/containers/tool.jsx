import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_EVENT_PARAMETERS} from '#/plugin/agenda/event/modals/parameters'
import {AgendaTool as AgendaToolComponent} from '#/plugin/agenda/tools/agenda/components/tool'
import {actions, selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaTool = connect(
  (state) => ({
    contextData: toolSelectors.contextData(state),
    currentUser: securitySelectors.currentUser(state),

    view: selectors.view(state),
    referenceDate: selectors.referenceDate(state),

    loaded: selectors.loaded(state),
    events: selectors.events(state)
  }),
  (dispatch) => ({
    changeView(view) {
      dispatch(actions.changeView(view))
    },
    changeReference(referenceDate) {
      dispatch(actions.changeReference(referenceDate))
    },
    loadEvents(rangeDates) {
      dispatch(actions.fetchEvents(rangeDates))
    },
    createEvent(event, user) {
      dispatch(modalActions.showModal(MODAL_EVENT_PARAMETERS, {
        event: merge({}, event, {
          meta: {
            creator: user
          }
        }),
        // TODO : only reload if event is created in the current reange
        onCreate: () => dispatch(actions.setLoaded(false))
      }))
    },
    importEvents(data, workspace = null) {
      dispatch(actions.import(data, workspace))
    }
  })
)(AgendaToolComponent)

export {
  AgendaTool
}
