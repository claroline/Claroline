import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/main/core/administration/integration'

const IntegrationTool = new App()

// mount the react application
bootstrap('.integration-container', IntegrationTool.component, IntegrationTool.store)
