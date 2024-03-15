import {connect} from 'react-redux'

import {PathMenu as PathMenuComponent} from '#/plugin/path/resources/path/components/menu'
import {selectors} from '#/plugin/path/resources/path/store'

const PathMenu = connect(
  (state) => ({
    overview: selectors.showOverview(state)
  })
)(PathMenuComponent)

export {
  PathMenu
}
