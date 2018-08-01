import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/main/core/tools/home'

// generate application
const AdminDesktopApp = new App()

// mount the react application
bootstrap('.desktop-container', AdminDesktopApp.component, AdminDesktopApp.store, AdminDesktopApp.initialData)
