import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ToolPage as ToolPageComponent} from '#/main/core/tool/components/page'
import {reducer, selectors} from '#/main/core/tool/store'

const ToolPage = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      name: selectors.name(state),
      currentContext: selectors.context(state)
    })
  )(ToolPageComponent)
)

export {
  ToolPage
}
