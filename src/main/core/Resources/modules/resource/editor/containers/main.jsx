import {PropTypes as T} from 'prop-types'

import {withReducer} from '#/main/app/store/reducer'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourceEditor as ResourceEditorComponent} from '#/main/core/resource/editor/components/main'
import {reducer} from '#/main/core/resource/editor/store'

const ResourceEditor = withReducer(resourceSelectors.EDITOR_NAME, reducer)(
  ResourceEditorComponent
)

ResourceEditor.propTypes = {
  defaultPage: T.string,
  // standard pages
  overviewPage: T.elementType,
  appearancePage: T.elementType,
  historyPage: T.elementType,
  permissionsPage: T.elementType,
  actionsPage: T.elementType,
  // custom pages
  pages: T.arrayOf(T.shape({

  })),
  /**
   * A func that returns some data to add to the Editor store on initialization.
   */
  additionalData: T.func,
  styles: T.array
}

export {
  ResourceEditor
}
