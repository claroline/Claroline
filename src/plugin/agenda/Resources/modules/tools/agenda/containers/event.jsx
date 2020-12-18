import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AgendaEvent as AgendaEventComponent} from '#/plugin/agenda/tools/agenda/components/event'
import {actions, selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaEvent = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),
      referenceDate: selectors.referenceDate(state),
      event: selectors.currentEvent(state)
    }),
    (dispatch) => ({
      update(event) {
        dispatch(actions.setLoaded(false))
        dispatch(actions.get(event.id))
      },
      delete(event, redirect) {
        dispatch(actions.delete(event)).then(redirect)
      },
      markDone(event) {
        dispatch(actions.markDone(event)).then(() => {
          dispatch(actions.get(event.id))
        })
      },
      markTodo(event) {
        dispatch(actions.markTodo(event)).then(() => {
          dispatch(actions.get(event.id))
        })
      }
    })
  )(AgendaEventComponent)
)

export {
  AgendaEvent
}
