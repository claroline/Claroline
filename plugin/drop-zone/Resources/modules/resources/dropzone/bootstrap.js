import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/drop-zone/resources/dropzone'

// generate application
const DropzoneApp = new App()

// mount the react application
bootstrap('.dropzone-container', DropzoneApp.component, DropzoneApp.store, DropzoneApp.initialData)
