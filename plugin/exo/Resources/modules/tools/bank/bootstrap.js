import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/plugin/exo/tools/bank'

// generate application
const BankToolApp = new App()

// mount the react application
bootstrap('.question-bank-container', BankToolApp.component, BankToolApp.store, BankToolApp.initialData)
