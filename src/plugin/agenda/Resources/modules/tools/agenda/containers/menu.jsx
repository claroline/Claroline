import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {constants as toolConstants} from '#/main/core/tool/constants'

import {AgendaMenu as AgendaMenuComponent} from '#/plugin/agenda/tools/agenda/components/menu'
import {selectors, actions} from '#/plugin/agenda/tools/agenda/store'

const AgendaMenu = withRouter(
  connect(
    (state) => ({
      multiplePlannings: toolConstants.TOOL_WORKSPACE !== toolSelectors.contextType(state),
      selected: selectors.referenceDateStr(state),
      view: selectors.view(state),
      types: selectors.types(state),
      plannings: selectors.plannings(state)
    }),
    (dispatch) => ({
      changeTypes(types) {
        dispatch(actions.changeTypes(types))
      },
      addPlanning(id, name) {
        dispatch(actions.addPlanning(id, name))
      },
      changePlanningColor(id, color) {
        dispatch(actions.changePlanningColor(id, color))
      },
      togglePlanning(id) {
        dispatch(actions.togglePlanning(id))
      },
      forcePlanning(id) {
        dispatch(actions.forcePlanning(id))
      },
      removePlanning(id) {
        dispatch(actions.removePlanning(id))
      }
    })
  )(AgendaMenuComponent)
)

export {
  AgendaMenu
}
