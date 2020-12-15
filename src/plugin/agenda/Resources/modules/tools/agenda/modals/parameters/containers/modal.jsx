import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as formSelectors} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/plugin/agenda/tools/agenda/modals/parameters/components/modal'
import {reducer, selectors} from '#/plugin/agenda/tools/agenda/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
