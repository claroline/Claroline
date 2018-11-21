import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/workspace/list'

// generate application
const WorkspaceListApp = new App()

// mount the react application
bootstrap('.workspaces-container', WorkspaceListApp.component, WorkspaceListApp.store)
