import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/tools/home'

// generate application
const HomeApp = new App()

// mount the react application
bootstrap('.home-container', HomeApp.component, HomeApp.store, HomeApp.initialData)
