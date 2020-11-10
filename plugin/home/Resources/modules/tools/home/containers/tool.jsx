import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {HomeTool as HomeToolComponent} from '#/plugin/home/tools/home/components/tool'
import {selectors} from '#/plugin/home/tools/home/store'

const HomeTool = withRouter(
  connect(
    (state) => ({
      editable: selectors.editable(state)
    })
  )(HomeToolComponent)
)

export {
  HomeTool
}
