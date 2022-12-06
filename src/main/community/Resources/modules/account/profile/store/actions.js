import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors} from '#/main/community/account/profile/store/selectors'

export const actions = {}

actions.load = (user) => (dispatch, getState) => {
  const formData = formSelectors.data(formSelectors.form(getState(), selectors.STORE_NAME))
  if (formData && formData.id === user.id) {
    return
  }

  return dispatch(formActions.reset(selectors.STORE_NAME, user, false))
}
