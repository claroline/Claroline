import {reducer} from '#/plugin/pdf-player/resources/pdf/reducer'
import {pdfPlayer} from '#/plugin/pdf-player/resources/pdf/components/resource'

export const App = () => ({
  component: pdfPlayer,
  store: reducer,
  styles: 'claroline-distribution-plugin-pdf-player-pdf-resource',
  initialData: (initialData) => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
})