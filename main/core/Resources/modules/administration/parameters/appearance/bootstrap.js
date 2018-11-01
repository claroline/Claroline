import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/administration/parameters/appearance'

// generate application
const AppSettings = new App()

// mount the react application
bootstrap('.appearance-settings-container', AppSettings.component, AppSettings.store, AppSettings.initialData)
