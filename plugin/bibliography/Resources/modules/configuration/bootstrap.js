import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/bibliography/configuration'

// generate application
const BookReferenceConfigurationApp = new App()

// mount the react application
bootstrap('.book-reference-configuration-container', BookReferenceConfigurationApp.component, BookReferenceConfigurationApp.store, BookReferenceConfigurationApp.initialData)
