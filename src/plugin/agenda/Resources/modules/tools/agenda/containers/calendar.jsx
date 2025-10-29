import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_EVENT_CREATION} from '#/plugin/agenda/event/modals/creation'
import {AgendaCalendar as AgendaCalendarComponent} from '#/plugin/agenda/tools/agenda/components/calendar'
import {actions, selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaCalendar = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentUser: securitySelectors.currentUser(state),

      view: selectors.view(state),
      referenceDate: selectors.referenceDate(state),

      loaded: selectors.loaded(state),
      events: selectors.events(state)
    }),
    (dispatch) => ({
      changeView(view, referenceDate) {
        dispatch(actions.changeView(view, referenceDate))
      },
      load(rangeDates) {
        return dispatch(actions.fetch(rangeDates))
      },
      create(event) {
        dispatch(modalActions.showModal(MODAL_EVENT_CREATION, {
          event: event,
          onCreate: (newEvent) => dispatch(actions.reload(newEvent, true))
        }))
      },
      reload(event, all = false) {
        dispatch(actions.reload(event, all))
      }
    })
  )(AgendaCalendarComponent)
)

export {
  AgendaCalendar
}
