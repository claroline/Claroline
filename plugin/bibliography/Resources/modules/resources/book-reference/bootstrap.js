import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/bibliography/resources/book-reference'

// generate application
const BookReferenceApp = new App()

// mount the react application
bootstrap('.book-reference-container', BookReferenceApp.component, BookReferenceApp.store, BookReferenceApp.initialData)
