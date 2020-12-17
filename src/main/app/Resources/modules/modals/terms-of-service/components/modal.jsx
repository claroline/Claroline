import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentHtml} from '#/main/app/content/components/html'

const TermsOfServiceModal = props =>
  <Modal
    {...omit(props, 'fetch')}
    icon="fa fa-fw fa-shield"
    title={trans('terms_of_service')}
    bsSize="lg"
    onEntering={() => {
      if (!props.loaded) {
        props.fetch()
      }
    }}
  >
    {!props.loaded &&
      <ContentLoader
        size="lg"
        description="Nous chargeons les conditions d'utilisation..."
      />
    }

    {(props.loaded && props.content) &&
      <ContentHtml className="modal-body">{props.content}</ContentHtml>
    }
  </Modal>

TermsOfServiceModal.propTypes = {
  fetch: T.func.isRequired,
  loaded: T.bool.isRequired,
  content: T.string,

  fadeModal: T.func.isRequired
}

export {
  TermsOfServiceModal
}
