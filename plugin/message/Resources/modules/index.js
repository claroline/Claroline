import {Messaging} from '#/plugin/message/components/messaging'

import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from '#/plugin/message/reducer'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.messaging-container',

  // app main component
  Messaging,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {

    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      }
    }
  }
)
