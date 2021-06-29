import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/main/core/tools/locations/material/modals/parameters/store'
import {MaterialParametersModal as MaterialParametersModalComponent} from '#/main/core/tools/locations/material/modals/parameters/components/modal'
import {Material as MaterialTypes} from '#/main/core/tools/locations/prop-types'

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
