import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

const TermsOfServiceModal = (props) =>
  <Modal
    {...props}
    icon="fa fa-fw fa-circle-info"
    title={trans('terms_of_service', {}, 'privacy')}
  >
    <div className="modal-body">
      hello
    </div>
  </Modal>

export {
  TermsOfServiceModal
}