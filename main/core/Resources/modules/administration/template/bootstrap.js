import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/administration/template'

// generate application
const templateApp = new App()

// mount the react application
bootstrap('.templates-container', templateApp.component, templateApp.store, templateApp.initialData)