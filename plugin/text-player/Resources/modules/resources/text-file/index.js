import {bootstrap} from '#/main/app/bootstrap'

// reducers
import {reducer} from '#/plugin/text-player/resources/text-file/reducer'
// Component
import {Resource} from '#/plugin/text-player/resources/text-file/components/resource'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.text-file-container',
  
  // app main component
  Resource,
  
  // app store configuration
  reducer,
  
  // initial data
  (initialData) => {
    return {
      resourceNode: initialData.resourceNode,
      textFile: initialData.textFile,
      isHtml: initialData.isHtml,
      content: initialData.content
    }
  }
)
