import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/exo/resources/quiz'

// generate application
const QuizApp = new App()

// mount the react application
bootstrap('.quiz-container', QuizApp.component, QuizApp.store, QuizApp.initialData)
