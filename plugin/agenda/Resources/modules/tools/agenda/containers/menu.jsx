import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {AgendaMenu as AgendaMenuComponent} from '#/plugin/agenda/tools/agenda/components/menu'
import {selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaMenu = withRouter(
  connect(
    (state) => ({
      selected: selectors.referenceDateStr(state),
      view: selectors.view(state)
    })
  )(AgendaMenuComponent)
)

export {
  AgendaMenu
}
