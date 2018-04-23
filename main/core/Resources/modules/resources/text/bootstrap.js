import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/main/core/resources/text'

// generate application
const TextApp = new App()

// mount the react application
bootstrap('.text-container', TextApp.component, TextApp.store)
