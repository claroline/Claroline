import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/team/tools/team'

// generate application
const teamApp = new App()

// mount the react application
bootstrap('.team-container', teamApp.component, teamApp.store, teamApp.initialData)