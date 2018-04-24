import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/path/resources/path'

// generate application
const PathApp = new App()

// mount the react application
bootstrap('.path-container', PathApp.component, PathApp.store, PathApp.initialData)
