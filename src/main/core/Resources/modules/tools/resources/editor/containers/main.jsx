import {withReducer} from '#/main/app/store/reducer'

import {ResourcesEditor as ResourcesEditorComponent} from '#/main/core/tools/resources/editor/components/main'
import {reducer, selectors} from '#/main/core/tools/resources/editor/store'

const ResourcesEditor = withReducer(selectors.STORE_NAME, reducer)(ResourcesEditorComponent)

export {
  ResourcesEditor
}
