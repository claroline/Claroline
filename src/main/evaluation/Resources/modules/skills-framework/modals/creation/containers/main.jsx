
import {withReducer} from '#/main/app/store/reducer'

import {CreationModal as CreationModalComponent} from '#/main/evaluation/skills-framework/modals/creation/components/main'
import {reducer, selectors} from '#/main/evaluation/skills-framework/modals/creation/store'

const CreationModal = withReducer(selectors.STORE_NAME, reducer)(
  CreationModalComponent
)

export {
  CreationModal
}
