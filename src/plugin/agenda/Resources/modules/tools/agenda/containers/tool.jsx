import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AgendaTool as AgendaToolComponent} from '#/plugin/agenda/tools/agenda/components/tool'
import {actions, reducer, selectors} from '#/plugin/agenda/tools/agenda/store'
import {withReducer} from '#/main/app/store/reducer'

const AgendaTool = withReducer(selectors.STORE_NAME, reducer)(
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
