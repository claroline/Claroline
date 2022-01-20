import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/main/evaluation/analytics/resource/evaluation/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {}, {
  invalidated: makeReducer(false, {
    [RESOURCE_LOAD]: () => true
  })
})

export {
  reducer
}
