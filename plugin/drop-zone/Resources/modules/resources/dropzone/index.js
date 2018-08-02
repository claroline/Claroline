import {reducer} from '#/plugin/drop-zone/resources/dropzone/reducer'

import {DropzoneResource} from '#/plugin/drop-zone/resources/dropzone/components/resource'

/**
 * Dropzone resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: DropzoneResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-drop-zone-dropzone-resource',
  initialData: (initialData) => ({
    user: initialData.user, // todo remove me can be found in the app
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    },
    dropzone: initialData.dropzone,
    myDrop: initialData.myDrop,
    nbCorrections: initialData.nbCorrections,
    tools: {
      data: initialData.tools,
      totalResults: initialData.tools.length
    },
    teams: initialData.teams,
    errorMessage: initialData.errorMessage
  })
})
