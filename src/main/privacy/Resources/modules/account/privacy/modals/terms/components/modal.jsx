import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentHtml} from '#/main/app/content/components/html'

const TermsModal = (props) => {
  console.log('TermsModal props', props)
  return(
    <Modal
      {...omit(props, 'fetch')}
      icon="fa fa-fw fa-shield"
      title={trans('terms_of_service')}
      bsSize="lg"
      onEntering={() => {
        if (!props.loaded) {
          props.reset(props.termsOfService)
        }
      }}
    >
      {!props.loaded &&
        <ContentLoader
          size="lg"
          description="Nous chargeons les conditions d'utilisation..."
        />
      }

      {(props.loaded && props.termsOfService) &&
        <ContentHtml className="modal-body">{props.termsOfService}</ContentHtml>
      }
    </Modal>
  )
}

TermsModal.propTypes = {
  fetch: T.func.isRequired,
  loaded: T.bool.isRequired,
  fadeModal: T.func,
  reset: T.func.isRequired,
  termsOfService: T.string.isRequired
}

export {
  TermsModal
}