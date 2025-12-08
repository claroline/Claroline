import {withReducer} from '#/main/app/store/reducer'

import {BadgeEditor as BadgeEditorComponent} from '#/plugin/open-badge/badge/editor/components/main'

import {reducer, selectors} from '#/plugin/open-badge/badge/editor/store'

const BadgeEditor = withReducer(selectors.STORE_NAME, reducer)(BadgeEditorComponent)

export {
  BadgeEditor
}
