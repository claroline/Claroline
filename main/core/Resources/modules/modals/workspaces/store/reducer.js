import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/modals/workspaces/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  filters: [{property: 'meta.personal', value: false}]
})

export {
  reducer
}
