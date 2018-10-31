import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/tools/progression'

// generate application
const ProgressionApp = new App()

// mount the react application
bootstrap('.progression-container', ProgressionApp.component, ProgressionApp.store, ProgressionApp.initialData)
