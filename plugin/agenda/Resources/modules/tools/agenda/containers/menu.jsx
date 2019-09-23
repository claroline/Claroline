import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {AgendaMenu as AgendaMenuComponent} from '#/plugin/agenda/tools/agenda/components/menu'
import {selectors, actions} from '#/plugin/agenda/tools/agenda/store'

const AgendaMenu = withRouter(
  connect(
    (state) => ({
      selected: selectors.referenceDateStr(state),
      view: selectors.view(state),
      types: selectors.types(state)
    }),
    (dispatch) => ({
      changeTypes(types) {
        dispatch(actions.changeTypes(types))
      }
    })
  )(AgendaMenuComponent)
)

export {
  AgendaMenu
}
