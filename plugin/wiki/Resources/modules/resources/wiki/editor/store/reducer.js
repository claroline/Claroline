import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME + '.wikiForm', {}, {
  originalData: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.wiki || state
  }),
  data: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.wiki || state
  })
})

export {
  reducer
}
