import {bootstrap} from '#/main/app/dom/bootstrap'

import AdminDesktopApp from '#/main/core/tools/home'

// mount the react application
bootstrap('.desktop-container', AdminDesktopApp.component, AdminDesktopApp.store, AdminDesktopApp.initialData)
