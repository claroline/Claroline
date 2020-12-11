import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/plugin/booking/tools/booking/material/modals/parameters/store'
import {MaterialParametersModal as MaterialParametersModalComponent} from '#/plugin/booking/tools/booking/material/modals/parameters/components/modal'
import {Material as MaterialTypes} from '#/plugin/booking/prop-types'

const MaterialParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadMaterial(material = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, material || MaterialTypes.defaultProps, !material))
      },
      saveMaterial(eventId = null, onSave = () => true) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, eventId ? ['apiv2_booking_material_update', {id: eventId}] : ['apiv2_booking_material_create'])).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(MaterialParametersModalComponent)
)

export {
  MaterialParametersModal
}
