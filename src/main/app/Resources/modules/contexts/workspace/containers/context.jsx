import {withReducer} from '#/main/app/store/components/withReducer'

import {WorkspaceContext as WorkspaceContextComponent} from '#/main/app/contexts/workspace/components/context'
import {reducer, selectors} from '#/main/app/contexts/workspace/store'

const WorkspaceContext = withReducer(selectors.STORE_NAME, reducer)(WorkspaceContextComponent)

export {
  WorkspaceContext
}
