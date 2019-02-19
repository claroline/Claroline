import {QuizCreation} from '#/plugin/exo/resources/quiz/containers/creation'
import {QuizResource} from '#/plugin/exo/resources/quiz/containers/resource'

import {registerDefaultItemTypes} from '#/plugin/exo/items/item-types'
import {registerDefaultContentItemTypes} from '#/plugin/exo/contents/content-types'

registerDefaultItemTypes()
registerDefaultContentItemTypes()

/**
 * Quiz creation application.
 */
export const Creation = () => ({
  component: QuizCreation
})

/**
 * Quiz resource application.
 */
export const App = () => ({
  component: QuizResource
})
