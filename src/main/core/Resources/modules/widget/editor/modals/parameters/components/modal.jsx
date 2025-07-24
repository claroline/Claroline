import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/widget/editor/modals/parameters/store'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetForm} from '#/main/core/widget/editor/components/form'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'save', 'widget', 'loadWidget')}
    title={trans('section')}
    onEntering={() => props.loadWidget(props.widget)}
  >
    <WidgetForm
      name={selectors.STORE_NAME}
      onSave={(formData) => {
        props.save(formData)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModal.propTypes = {
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  loadWidget: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
