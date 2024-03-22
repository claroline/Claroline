import {connect} from 'react-redux'

import {PublicMenu as PublicMenuComponent} from '#/main/app/contexts/public/components/menu'
import {selectors as contextSelectors} from '#/main/app/context/store'

const PublicMenu = connect(
  (state) => ({
    tools: contextSelectors.tools(state)
  })
)(PublicMenuComponent)

export {
  PublicMenu
}
