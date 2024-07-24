import {connect} from 'react-redux'

import {AdministrationMenu as AdministrationMenuComponent} from '#/main/app/contexts/administration/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const AdministrationMenu = connect(
  (state) => ({
    tools: contextSelectors.tools(state)
  })
)(AdministrationMenuComponent)

export {
  AdministrationMenu
}
