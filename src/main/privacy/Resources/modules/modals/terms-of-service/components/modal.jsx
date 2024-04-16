import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentHtml} from '#/main/app/content/components/html'

const TermsOfServiceModal = props =>
  <Modal
    {...omit(props, 'fetch')}
    icon="fa fa-fw fa-file-shield"
    title={trans('terms_of_service',{},'privacy')}
    size="lg"
    backdrop="static"
    closeButton={false}
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

    {props.loaded && props.validate &&
      <div className="modal-footer">
        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-primary"
          label={trans('terms_of_service_accept', {}, 'privacy')}
          callback={() => props.accept().then(() => {
            props.onAccept()
            props.fadeModal()
          })}
        />
        <Button
          className="btn btn-danger"
          type={CALLBACK_BUTTON}
          label={trans('terms_of_service_refuse', {}, 'privacy')}
          dangerous={true}
          callback={() => {
            props.onRefuse()
            props.fadeModal()
          }}
        />
      </div>
    }
  </Modal>

TermsOfServiceModal.propTypes = {
  fetch: T.func.isRequired,
  loaded: T.bool.isRequired,
  content: T.string,

  // validation props
  validate: T.bool,
  accept: T.func.isRequired,
  onAccept: T.func,
  onRefuse: T.func,

  fadeModal: T.func.isRequired,
  formData: T.object.isRequired
}

TermsOfServiceModal.defaultProps = {
  validate: false,
  onAccept: () => true,
  onRefuse: () => true
}

export {
  TermsOfServiceModal
}
