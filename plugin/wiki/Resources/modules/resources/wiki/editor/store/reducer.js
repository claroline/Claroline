import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME + '.wikiForm')

export {
  reducer
}
