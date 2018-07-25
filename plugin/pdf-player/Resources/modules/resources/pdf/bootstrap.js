import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/pdf-player/resources/pdf'

// generate application
const pdfPlayerApp = new App()

// mount the react application
bootstrap('.pdf-player-container', pdfPlayerApp.component, pdfPlayerApp.store, pdfPlayerApp.initialData)