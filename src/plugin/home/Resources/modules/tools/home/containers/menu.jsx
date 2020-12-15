import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {HomeMenu as HomeMenuComponent} from '#/plugin/home/tools/home/components/menu'

const HomeMenu = withRouter(
  connect(
    (state) => ({
      canEdit: hasPermission('edit', toolSelectors.toolData(state))
    })
  )(HomeMenuComponent)
)

export {
  HomeMenu
}
