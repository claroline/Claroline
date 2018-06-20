import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/forum/resources/forum'

// generate application
const ForumApp = new App()

// mount the react application
bootstrap('.forum-container', ForumApp.component, ForumApp.store, ForumApp.initialData)
