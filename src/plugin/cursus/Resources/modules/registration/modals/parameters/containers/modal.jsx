import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {withReducer} from '#/main/app/store/reducer'
import {actions, actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/plugin/cursus/registration/modals/parameters/components/modal'
import {selectors, reducer} from '#/plugin/cursus/registration/modals/parameters/store'
import {isFieldDisplayed} from '#/main/app/content/form/parameters/utils'
import {notEmpty} from '#/main/app/data/types/validators'
import {cleanErrors} from '#/main/app/content/form/utils'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME)),
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset(registrationData) {
        dispatch(formActions.reset(selectors.STORE_NAME, registrationData, isEmpty(registrationData) || !registrationData.id))
      },
      save(fields, formData, onSave) {
        const errors = {
          data: {}
        }

        const requiredFields = fields.filter(field => field.required && isFieldDisplayed(field, fields, formData.data))
        errors.data = requiredFields.reduce((fieldErrors, field) => Object.assign(fieldErrors, {
          [field.id]: notEmpty(get(formData, `data[${field.id}]`))
        }), {})

        dispatch(formActions.setErrors(selectors.STORE_NAME, errors))
        dispatch(actions.submit(selectors.STORE_NAME))

        if (isEmpty(cleanErrors({}, errors))) {
          onSave(formData)
        }
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
