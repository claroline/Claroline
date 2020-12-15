import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/administration/parameters/store'
import {actions as iconActions} from '#/main/theme/administration/appearance/icon/store'
import {IconItemFormModal as IconItemFormModalComponent} from '#/main/theme/administration/appearance/icon/modals/icon-item/components/modal'

const IconItemFormModal = connect(
  (state) => ({
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME+'.icons.item')),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME+'.icons.item')),
    iconItem: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.icons.item'))
  }),
  (dispatch) => ({
    updateIconItem(iconSet, iconItem) {
      dispatch(iconActions.updateIconItem(iconSet, iconItem))
    }
  })
)(IconItemFormModalComponent)

export {
  IconItemFormModal
}
