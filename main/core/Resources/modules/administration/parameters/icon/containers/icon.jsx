import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store/actions'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/parameters/store'
import {actions} from '#/main/core/administration/parameters/icon/store'
import {MODAL_ICON_ITEM_FORM} from '#/main/core/administration/parameters/modals/icon-item'
import {Icon as IconComponent} from '#/main/core/administration/parameters/icon/components/icon'

const Icon = connect(
  (state) => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME+'.icons.current')),
    iconSet: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.icons.current')),
    mimeTypes: selectors.mimeTypes(state)
  }),
  (dispatch) => ({
    openIconItemForm(iconSet, mimeTypes, defaultProps, id = null) {
      dispatch(actions.openIconItemForm(selectors.STORE_NAME+'.icons.item', defaultProps, id))
      dispatch(modalActions.showModal(MODAL_ICON_ITEM_FORM, {
        mimeTypes: mimeTypes,
        iconSet: iconSet
      }))
    }
  })
)(IconComponent)

export {
  Icon
}
