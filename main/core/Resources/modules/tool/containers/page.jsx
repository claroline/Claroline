import {connect} from 'react-redux'

import {selectors} from '#/main/core/tool/store'

// the component to connect (for now a simple Page)
import {PageContainer} from '#/main/core/layout/page/containers/page'

const ToolPageContainer = connect(
  (state) => ({
    editable: selectors.editable(state)
  })
)(PageContainer)

export {
  ToolPageContainer
}
