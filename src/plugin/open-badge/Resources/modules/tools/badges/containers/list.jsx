import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeList as BadgeListComponent} from '#/plugin/open-badge/tools/badges/components/list'

const BadgeList = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
  })
)(BadgeListComponent)

export {
  BadgeList
}
