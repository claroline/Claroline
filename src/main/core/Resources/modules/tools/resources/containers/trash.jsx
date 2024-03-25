import {connect} from 'react-redux'

import {ResourcesTrash as ResourcesTrashComponent} from '#/main/core/tools/resources/components/trash'
import {selectors as toolSelectors} from '#/main/core/tool/store'

const ResourcesTrash = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextId: toolSelectors.contextId(state)
  })
)(ResourcesTrashComponent)

export {
  ResourcesTrash
}
