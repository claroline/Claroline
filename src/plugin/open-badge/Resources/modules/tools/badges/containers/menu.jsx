import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeMenu as BadgeMenuComponent} from '#/plugin/open-badge/tools/badges/components/menu'

const BadgeMenu = connect(
  state => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state))
  })
)(BadgeMenuComponent)

export {
  BadgeMenu
}
