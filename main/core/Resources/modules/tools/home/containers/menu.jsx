import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {HomeMenu as HomeMenuComponent} from '#/main/core/tools/home/components/menu'
import {selectors} from '#/main/core/tools/home/store'

const HomeMenu = withRouter(
  connect(
    (state) => ({
      editable: selectors.editable(state)
    })
  )(HomeMenuComponent)
)

export {
  HomeMenu
}
