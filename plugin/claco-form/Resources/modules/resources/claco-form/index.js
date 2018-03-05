import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {registerModals} from '#/main/core/layout/modal'
import {makeResourceReducer} from '#/main/core/resource/reducer'

import {
  resourceReducers,
  mainReducers,
  parametersReducers,
  messageReducers
} from './reducers'
import {categoryReducers} from './editor/category/reducers'
import {keywordReducers} from './editor/keyword/reducers'
import {fieldReducers} from './editor/field/reducers'
import {
  reducer,
  myEntriesCountReducers,
  currentEntryReducers
} from './player/entry/reducers'
import {ClacoFormResource} from './components/resource.jsx'
import {CategoryFormModal} from './editor/category/components/category-form-modal.jsx'
import {KeywordFormModal} from './editor/keyword/components/keyword-form-modal.jsx'
import {FieldFormModal} from './editor/field/components/field-form-modal.jsx'

// register custom modals
registerModals([
  ['MODAL_CATEGORY_FORM', CategoryFormModal],
  ['MODAL_KEYWORD_FORM', KeywordFormModal],
  ['MODAL_FIELD_FORM', FieldFormModal]
])

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.claco-form-container',

  // app main component
  ClacoFormResource,

  // app store configuration
  makeResourceReducer({}, {
    user: mainReducers,
    resource: resourceReducers,
    canEdit: mainReducers,
    isAnon: mainReducers,
    canGeneratePdf: mainReducers,
    parameters: parametersReducers,
    categories: categoryReducers,
    keywords: keywordReducers,
    fields: fieldReducers,
    entries: reducer,
    myEntriesCount: myEntriesCountReducers,
    currentEntry: currentEntryReducers,
    cascadeLevelMax: mainReducers,
    message: messageReducers,
    roles: mainReducers,
    myRoles: mainReducers
  }),

  // transform data attributes for redux store
  (initialData) => {
    const resource = initialData.resource

    return {
      user: initialData.user,
      resource: resource,
      resourceNode: initialData.resourceNode,
      isAnon: !initialData.user,
      canGeneratePdf: initialData.canGeneratePdf === 1,
      parameters: Object.assign({}, resource.details, {'activePanelKey': ''}),
      categories: resource.categories,
      keywords: resource.keywords,
      fields: initialData.fields,
      entries: initialData.entries,
      myEntriesCount: initialData.myEntriesCount,
      cascadeLevelMax: initialData.cascadeLevelMax,
      roles: initialData.roles,
      myRoles: initialData.myRoles
    }
  }
)
