import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AgendaTool as AgendaToolComponent} from '#/plugin/agenda/tools/agenda/components/tool'
import {actions} from '#/plugin/agenda/tools/agenda/store'

const AgendaTool = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    }),
    (dispatch) => ({
      loadEvent(eventId) {
        dispatch(actions.get(eventId))
      }
    })
  )(AgendaToolComponent)
)

export {
  AgendaTool
}
