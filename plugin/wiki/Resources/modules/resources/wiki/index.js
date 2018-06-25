import {WikiResource} from '#/plugin/wiki/resources/wiki/components/resource'
import {reducer} from '#/plugin/wiki/resources/wiki/store'

/**
 * Wiki resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: WikiResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-wiki-wiki-resource',
  initialData: initialData => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode
    },
    wiki: initialData.wiki,
    sections: {
      tree: initialData.sections
    },
    exportPdfEnabled: initialData.exportPdfEnabled
  })
})