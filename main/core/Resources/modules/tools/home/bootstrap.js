import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/main/core/tools/home'

// generate application
const HomeToolApp = new App()

// mount the react application
bootstrap('.home-container', HomeToolApp.component, HomeToolApp.store, HomeToolApp.initialData)
