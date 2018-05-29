import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/main/core/resources/text'

// generate application
const DirectoryApp = new App()

// mount the react application
bootstrap('.directory-container', DirectoryApp.component, DirectoryApp.store)
