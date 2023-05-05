import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/administration/connection-messages/store/selectors'
import {MODAL_SLIDE_FORM} from '#/main/core/administration/connection-messages/modals/slide'
import {Message as MessageComponent} from '#/main/core/administration/connection-messages/components/message'

const Message = connect(
  (state) => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.current')),
    message: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.current'))
  }),
  (dispatch) => ({
    createSlide(slideIndex) {
      dispatch(
        modalActions.showModal(MODAL_SLIDE_FORM, {
          formName: selectors.STORE_NAME+'.current',
          dataPart: `slides.${slideIndex}`,
          title: trans('content_creation')
        })
      )
    },
    updateProp(prop, value) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.current', prop, value))
    }
  })
)(MessageComponent)

export {
  Message
}
