import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {registerModals} from '#/main/core/layout/modal'

import {reducer} from '#/plugin/drop-zone/resources/dropzone/reducer'

import {registerDropzoneTypes} from '#/plugin/drop-zone/data/types'
import {DropzoneResource} from '#/plugin/drop-zone/resources/dropzone/components/resource.jsx'
import {CorrectionModal} from '#/plugin/drop-zone/resources/dropzone/correction/components/modal/correction-modal.jsx'

registerDropzoneTypes()

registerModals([
  ['MODAL_CORRECTION', CorrectionModal]
])

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.dropzone-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  DropzoneResource,

  // app store configuration
  reducer,

  // transform data attributes for redux store
  (initialData) => {
    return {
      user: initialData.user,
      resourceNode: initialData.resourceNode,
      dropzone: initialData.dropzone,
      myDrop: initialData.myDrop,
      nbCorrections: initialData.nbCorrections,
      tools: {
        data: initialData.tools,
        totalResults: initialData.tools.length
      },
      userEvaluation: initialData.userEvaluation,
      teams: initialData.teams,
      errorMessage: initialData.errorMessage
    }
  }
)
