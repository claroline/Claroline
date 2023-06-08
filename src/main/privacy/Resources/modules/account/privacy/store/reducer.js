import {selectors} from '#/main/privacy/account/privacy/store/selectors'
import {makeReducer} from '#/main/app/store/reducer'

const reducer = makeReducer(selectors.STORE_NAME)

export {reducer}
