import {withReducer} from '#/main/app/store/reducer'

import {WorkspacesEditor as WorkspacesEditorComponent} from '#/main/core/tools/workspaces/editor/components/main'
import {reducer, selectors} from '#/main/core/tools/workspaces/editor/store'

const WorkspacesEditor = withReducer(selectors.STORE_NAME, reducer)(WorkspacesEditorComponent)

export {
  WorkspacesEditor
}
