import React from 'react'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/privacy/administration/privacy/store'
import {trans} from '#/main/app/intl'
import omit from 'lodash/omit'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

const TermsOfServiceModal = (props) =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-solid fa-pen-to-square"
    title={trans('terms_of_service', {}, 'privacy')}
  >
    hello
  </Modal>

TermsOfServiceModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func,
  parameters: T.shape({
    tos: T.shape({
      enabled: T.bool
    })
  })
}
export {
  TermsOfServiceModal
}
