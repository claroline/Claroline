import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {ParametersModal as ParametersModalComponent} from '#/main/authentication/integration/ips/modals/parameters/components/modal'
import {reducer, selectors} from '#/main/authentication/integration/ips/modals/parameters/store'

const ParametersModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME)),
      data: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME, prop, value))
      },
      loadIp(ip = null) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, ip, !ip))
      },
      saveIp(ip, isNew, callback) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, isNew ? ['apiv2_ip_user_create'] : ['apiv2_ip_user_update', {id: ip.id}])).then(() => {
          if (callback) {
            callback(ip)
          }
        })
      }
    })
  )(ParametersModalComponent)
)

export {
  ParametersModal
}
