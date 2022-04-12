import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {BadgeMenu as BadgeMenuComponent} from '#/plugin/open-badge/tools/badges/components/menu'

const BadgeMenu = connect(
  (state) => ({
    contextType: toolSelectors.contextType(state),
    workspace: toolSelectors.contextData(state)
  })
)(BadgeMenuComponent)

export {
  BadgeMenu
}
