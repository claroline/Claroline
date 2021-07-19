import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/quota/store/selectors'

export const reducer = makeListReducer(selectors.STORE_NAME, {
})
