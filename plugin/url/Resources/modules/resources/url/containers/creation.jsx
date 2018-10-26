import {connect} from 'react-redux'

import {UrlForm} from '#/plugin/url/resources/url/components/creation'
import {selectors} from '#/main/core/resource/modals/creation/store'
import {actions as formActions} from '#/main/app/content/form/store/actions'

const UrlCreation = connect(
  (state) => ({
    newNode: selectors.newNode(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(
        formActions.updateProp(
          selectors.STORE_NAME, selectors.FORM_RESOURCE_PART+'.'+propName, propValue
        )
      )
    }
  })
)(UrlForm)


export {
  UrlCreation
}
