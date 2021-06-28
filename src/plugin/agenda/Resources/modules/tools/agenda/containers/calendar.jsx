import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import moment from 'moment'

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
      contextData: toolSelectors.contextData(state),
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
      create(event, context, user) {
        const end = moment(event.start, 'YYYY-MM-DDThh:mm:ss')
        // default event duration to 1 hour
        end.add(1, 'h')
        dispatch(modalActions.showModal(MODAL_EVENT_CREATION, {
          event: merge({}, event, {
            workspace: !isEmpty(context) ? context : null,
            end: end.format('YYYY-MM-DDThh:mm:ss'),
            meta: {
              creator: user
            }
          }),
          onSave: (newEvent) => dispatch(actions.reload(newEvent, true))
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
