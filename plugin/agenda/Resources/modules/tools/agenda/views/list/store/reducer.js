import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/plugin/agenda/tools/agenda/views/list/store/selectors'

export const reducer = makeListReducer(selectors.STORE_NAME, {}, {

})