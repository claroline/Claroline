import {makeReducer} from '#/main/core/scaffolding/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

// app reducers
import {reducer as editorReducer} from '#/plugin/wiki/resources/wiki/editor/store'
import {reducer as historyReducer} from '#/plugin/wiki/resources/wiki/history/store'
import {reducer as sectionsReducer} from '#/plugin/wiki/resources/wiki/player/store'
import {reducer as deletedSectionsReducer} from '#/plugin/wiki/resources/wiki/deleted/store'

const wikiReducer = makeReducer({}, {
  [FORM_SUBMIT_SUCCESS+'/wikiForm']: (state, action) => action.updatedData
})

const reducer = {
  wiki: wikiReducer,
  wikiForm: editorReducer,
  sections: sectionsReducer,
  deletedSections: deletedSectionsReducer,
  history: historyReducer,
  exportPdfEnabled: makeReducer(false, {})
}

export {
  reducer
}