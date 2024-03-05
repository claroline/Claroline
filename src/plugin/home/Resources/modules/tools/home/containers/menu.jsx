import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomeMenu as HomeMenuComponent} from '#/plugin/home/tools/home/components/menu'

const HomeMenu = connect(
  (state) => ({
    path: toolSelectors.path(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state))
  })
)(HomeMenuComponent)

export {
  HomeMenu
}
