import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/evaluation/sequence/editor/store/selectors'

export const actions = {}

actions.reset = (badge) => formActions.reset(selectors.STORE_NAME, badge)

actions.update = (value, propPath = null) => {
  if (propPath) {
    return formActions.updateProp(selectors.STORE_NAME, propPath, value)
  }

  return formActions.update(selectors.STORE_NAME, value)
}
