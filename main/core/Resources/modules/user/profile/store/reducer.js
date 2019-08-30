import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {
  PROFILE_FACET_OPEN
} from '#/main/core/user/profile/store/actions'

import {makeListReducer} from '#/main/app/content/list/store'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as select} from '#/main/core/user/profile/store/selectors'
//celui là devrait pas forcément etre là
import {selectors} from '#/main/core/tools/community/store'

const reducer = combineReducers({
  currentFacet: makeReducer(null, {
    [PROFILE_FACET_OPEN]: (state, action) => action.id
  }),
  //this should be a new makeReducer for the profile imo
  contacts: makeListReducer(selectors.STORE_NAME + '.profile.contacts', {}),
  facets: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.facets}
  ),
  user: makeFormReducer(select.FORM_NAME),
  loaded: makeReducer(false, {
    ['FORM_RESET/' + select.FORM_NAME]: () => true
  }),
  parameters: makeReducer({}, {[makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters ? action.toolData.parameters: {}}),
  badges: combineReducers({
    mine: makeListReducer('badges.mine', {})
  })
})

export {
  reducer
}
