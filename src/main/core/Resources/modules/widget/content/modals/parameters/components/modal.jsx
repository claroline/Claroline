import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {WidgetContentForm} from '#/main/core/widget/content/components/form'
import {selectors} from '#/main/core/widget/content/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'instance', 'saveEnabled', 'save','loadContent', 'formData', 'currentContext')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    onEntering={() => props.loadContent(props.content)}
    size="lg"
  >
    {!isEmpty(props.formData) &&
      <WidgetContentForm level={5} name={selectors.STORE_NAME} currentContext={props.currentContext} />
    }

    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={CALLBACK_BUTTON}
      primary={true}
      disabled={!props.saveEnabled}
      label={trans('save', {}, 'actions')}
      callback={() => {
        props.save(props.formData)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  currentContext: T.object,
  formData: T.shape({}),
  content: T.shape({}),
  loadContent: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
