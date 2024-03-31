import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeList as BadgeListComponent} from '#/plugin/open-badge/tools/badges/badge/components/list'

const BadgeList = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state))
  })
)(BadgeListComponent)

export {
  BadgeList
}
