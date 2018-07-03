import {Player} from '#/plugin/lesson/resources/lesson/player/components/player'
import {reducer} from '#/plugin/lesson/resources/lesson/store'



/**
 * Lesson resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: Player,
  store: reducer,
  styles: 'claroline-distribution-plugin-lesson-lesson-resource',
  initialData: initialData => Object.assign({}, initialData, {
    lesson: initialData.lesson,
    resource: {
      node: initialData.resourceNode
    },
    chapter: initialData.chapter,
    tree: {
      data: initialData.tree
    },
    exportPdfEnabled: initialData.exportPdfEnabled
  })
})