import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/tools/trash'

// generate application
const TrashApp = new App()

// mount the react application
bootstrap('.trash-container', TrashApp.component, TrashApp.store, TrashApp.initialData)
