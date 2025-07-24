import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {WidgetContentForm} from '#/main/core/widget/content/components/form'
import {selectors} from '#/main/core/widget/content/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'instance', 'saveEnabled', 'save','loadContent', 'formData', 'currentContext')}
    title={trans('widget', {}, 'widget')}
    onEntering={() => props.loadContent(props.content)}
  >
    <WidgetContentForm
      level={5}
      name={selectors.STORE_NAME}
      currentContext={props.currentContext}
      onSave={(formData) => {
        props.save(formData)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  currentContext: T.object,
  content: T.shape({}),
  loadContent: T.func.isRequired,
  save: T.func,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
