import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AgendaEvent as AgendaEventComponent} from '#/plugin/agenda/tools/agenda/components/event'
import {selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaEvent = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      referenceDate: selectors.referenceDate(state),
      event: selectors.currentEvent(state)
    })
  )(AgendaEventComponent)
)

export {
  AgendaEvent
}
