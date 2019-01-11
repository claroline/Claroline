import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/plugin/lti/administration/lti'

// generate application
const ltiApp = new App()

// mount the react application
bootstrap('.lti-container', ltiApp.component, ltiApp.store, ltiApp.initialData)