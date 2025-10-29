import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {makeFetchReducer} from '#/main/app/api/fetch/store'
import {API_FETCH_INVALIDATE} from '#/main/app/api/fetch/store/actions'

import {selectors} from '#/plugin/cursus/event/store/selectors'
import {constants} from '#/plugin/cursus/constants'

export const reducer = makeFetchReducer(selectors.STORE_NAME, {}, {
  users: makeListReducer(selectors.STORE_NAME+'.users', {
    sortBy: {property: 'date', direction: -1},
    filters: {filters: [{property: 'type', value: constants.LEARNER_TYPE, locked: true, hidden: true}]}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(API_FETCH_INVALIDATE, selectors.STORE_NAME)]: () => true
    })
  })
})
