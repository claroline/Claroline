import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {HomeTool as HomeToolComponent} from '#/main/core/tools/home/components/tool'
import {selectors} from '#/main/core/tools/home/store'

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
