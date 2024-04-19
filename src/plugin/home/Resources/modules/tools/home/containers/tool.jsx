import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {HomeTool as HomeToolComponent} from '#/plugin/home/tools/home/components/tool'
import {reducer, selectors} from '#/plugin/home/tools/home/store'
import {selectors as playerSelectors} from '#/plugin/home/tools/home/player/store'

const HomeTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      tabs: playerSelectors.tabs(state)
    })
  )(HomeToolComponent)
)

export {
  HomeTool
}
