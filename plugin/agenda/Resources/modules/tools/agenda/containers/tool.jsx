import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AgendaTool as AgendaToolComponent} from '#/plugin/agenda/tools/agenda/components/tool'
import {actions, selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaTool = connect(
  (state) => ({
    contextData: toolSelectors.contextData(state),

    view: selectors.view(state),
    referenceDate: selectors.referenceDate(state)
  }),
  (dispatch) => ({
    changeView(view) {
      dispatch(actions.changeView(view))
    },
    changeReference(referenceDate) {
      dispatch(actions.changeReference(referenceDate))
    },

    import(data, workspace = null) {
      dispatch(actions.import(data, workspace))
    }
  })
)(AgendaToolComponent)

export {
  AgendaTool
}
