
import {withReducer} from '#/main/app/store/components/withReducer'

import {reducer, selectors} from '#/main/core/modals/workspaces/store'
import {WorkspacesModal} from '#/main/core/modals/workspaces/components/modal'

/*const WorkspacesModal = withReducer(selectors.STORE_NAME, reducer)(
  WorkspacesModalComponent
)*/

export {
  WorkspacesModal
}
