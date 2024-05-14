import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'
import {selectors} from '#/plugin/cursus/tools/events/store/selectors'
import {selectors as courseSelectors} from '#/plugin/cursus/course/store'

const reducer = combineReducers({
  events: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'startDate', direction: -1},
    filters: [{property: 'status', value: 'not_ended'}]
  }),
  presences: makeListReducer(selectors.STORE_NAME+'.presences', {
    sortBy: {property: 'user', direction: 1}
  }),
  course: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => {
      return typeof action.toolData.course !== 'undefined' ? action.toolData.course : null
    },
    [FORM_SUBMIT_SUCCESS+'/'+courseSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
})

export {
  reducer
}
