import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentHtml} from '#/main/app/content/components/html'

const TermsModal = (props) => 
  <Modal
    {...omit(props, 'fetch')}
    icon="fa fa-fw fa-shield"
    title={trans('terms_of_service')}
    bsSize="lg"
  >
    {(props.loaded && props.termsOfService) &&
        <ContentHtml className="modal-body">{props.termsOfService}</ContentHtml>
    }
  </Modal>

TermsModal.propTypes = {
  fetch: T.func.isRequired,
  fadeModal: T.func,
  termsOfService: T.string.isRequired
}

export {
  TermsModal
}