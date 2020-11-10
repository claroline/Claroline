import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {HomeMenu as HomeMenuComponent} from '#/plugin/home/tools/home/components/menu'
import {selectors} from '#/plugin/home/tools/home/store'

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
