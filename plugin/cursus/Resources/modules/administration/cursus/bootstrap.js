import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/cursus/administration/cursus'

// generate application
const cursusApp = new App()

// mount the react application
bootstrap('.cursus-container', cursusApp.component, cursusApp.store, cursusApp.initialData)