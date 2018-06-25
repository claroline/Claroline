import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/wiki/resources/wiki'

// generate application
const WikiApp = new App()

// mount the react application
bootstrap('.wiki-container', WikiApp.component, WikiApp.store, WikiApp.initialData)