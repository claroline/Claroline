import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/plugin/competency/administration/competency'

// generate application
const competencyApp = new App()

// mount the react application
bootstrap('.competencies-container', competencyApp.component, competencyApp.store, competencyApp.initialData)