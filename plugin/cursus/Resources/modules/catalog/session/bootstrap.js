import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/plugin/cursus/catalog/session'

// generate application
const catalogSessionApp = new App()

// mount the react application
bootstrap('.cursus-catalog-session-container', catalogSessionApp.component, catalogSessionApp.store, catalogSessionApp.initialData)