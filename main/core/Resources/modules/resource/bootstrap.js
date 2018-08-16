import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/main/core/resource'

// generate application
const ResourceApp = new App()

// mount the react application
bootstrap('.resource-container', ResourceApp.component, ResourceApp.store, ResourceApp.initialData)
