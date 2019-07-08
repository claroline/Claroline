import moment from 'moment'

import {now, getApiFormat} from '#/main/app/intl/date'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  AGENDA_CHANGE_VIEW,
  AGENDA_CHANGE_REFERENCE
} from '#/plugin/agenda/tools/agenda/store/actions'

const reducer = combineReducers({
  view: makeReducer('month', {
    [AGENDA_CHANGE_VIEW]: (state, action) => action.view
  }),

  referenceDate: makeReducer(now(), {
    [AGENDA_CHANGE_REFERENCE]: (state, action) => moment(action.referenceDate).format(getApiFormat())
  })
})

export {
  reducer
}
