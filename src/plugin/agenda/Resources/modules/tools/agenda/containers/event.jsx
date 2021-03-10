import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AgendaEvent as AgendaEventComponent} from '#/plugin/agenda/tools/agenda/components/event'
import {actions, selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaEvent = connect(
  (state) => ({
    path: toolSelectors.path(state),
    event: selectors.currentEvent(state)
  }),
  (dispatch) => ({
    reload(event) {
      dispatch(actions.reload(event))
    }
  })
)(AgendaEventComponent)

export {
  AgendaEvent
}
