import {withReducer} from '#/main/app/store/reducer'

import {BadgesEditor as BadgesEditorComponent} from '#/plugin/open-badge//tools/badges/editor/components/main'
import {reducer, selectors} from '#/plugin/open-badge//tools/badges/editor/store'

const BadgesEditor = withReducer(selectors.STORE_NAME, reducer)(BadgesEditorComponent)

export {
  BadgesEditor
}
