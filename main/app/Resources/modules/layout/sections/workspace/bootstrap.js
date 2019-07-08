import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/app/layout/sections/workspace'

// generate application
const WorkspaceApp = new App()

// mount the react application
bootstrap('.workspace-container', WorkspaceApp.component, WorkspaceApp.store)
