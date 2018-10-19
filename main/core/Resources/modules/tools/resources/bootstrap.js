import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/tools/resources'

// generate application
const ResourcesApp = new App()

// mount the react application
bootstrap('.resources-container', ResourcesApp.component, ResourcesApp.store, ResourcesApp.initialData)
