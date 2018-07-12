import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/announcement/resources/announcement'

// generate application
const AnnouncementApp = new App()

// mount the react application
bootstrap('.announcement-container', AnnouncementApp.component, AnnouncementApp.store, AnnouncementApp.initialData)
