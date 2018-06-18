import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/claco-form/resources/claco-form'

// generate application
const ClacoFormApp = new App()

// mount the react application
bootstrap('.claco-form-container', ClacoFormApp.component, ClacoFormApp.store, ClacoFormApp.initialData)
