import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentHtml} from '#/main/app/content/components/html'
import {trans} from '#/main/app/intl/translation'

const TermsModal = (props) => 
  <Modal
    {...omit(props, 'fetch', 'loaded', 'termsOfService')}
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
  loaded: T.bool.isRequired,
  termsOfService: T.string.isRequired
}

export {
  TermsModal
}