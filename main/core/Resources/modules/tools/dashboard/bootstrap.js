import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/tools/dashboard'

// generate application
const DashboardApp = new App()

// mount the react application
bootstrap('.dashboard-container', DashboardApp.component, DashboardApp.store, DashboardApp.initialData)
