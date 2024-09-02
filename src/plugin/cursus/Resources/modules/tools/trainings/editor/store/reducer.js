import {makeListReducer} from '#/main/app/content/list/store'
import {selectors} from '#/plugin/cursus/tools/trainings/editor/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'name', direction: 1}
})

export {
  reducer
}
