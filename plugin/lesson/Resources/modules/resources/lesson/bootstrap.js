import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/lesson/resources/lesson'

// generate application
const LessonApp = new App()

// mount the react application
bootstrap('.lesson-container', LessonApp.component, LessonApp.store, LessonApp.initialData)