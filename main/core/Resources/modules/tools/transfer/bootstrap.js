import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/tools/transfer'

// generate application
const TransferApp = new App()

// mount the react application
bootstrap('.transfer-container', TransferApp.component, TransferApp.store, TransferApp.initialData)
