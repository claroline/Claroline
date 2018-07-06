import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/scorm/resources/scorm'

// generate application
const ScormApp = new App()

// mount the react application
bootstrap('.scorm-container', ScormApp.component, ScormApp.store, ScormApp.initialData)
