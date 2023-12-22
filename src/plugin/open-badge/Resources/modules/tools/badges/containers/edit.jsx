import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/plugin/open-badge/tools/badges/store'
import {BadgeEdit as BadgeEditComponent} from '#/plugin/open-badge/tools/badges/components/edit'

const BadgeEdit = connect(
  state => ({
    path: toolSelectors.path(state),
    badge: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  (dispatch) => ({
    reload(id) {
      dispatch(actions.openBadge(selectors.FORM_NAME, id))
    }
  })
)(BadgeEditComponent)

export {
  BadgeEdit
}
