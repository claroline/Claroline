import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {selectors} from '#/main/core/widget/editor/modals/creation/store'
import {WidgetForm} from '#/main/core/widget/editor/components/form'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

const MODAL_WIDGET_CREATION_PARAMETERS = 'MODAL_WIDGET_CREATION_PARAMETERS'

const ParametersModalComponent = props =>
  <Modal
    {...omit(props, 'widget', 'saveEnabled', 'create', 'dispatch')}
    icon="fa fa-fw fa-plus"
    title={trans('new_section')}
    subtitle={trans('new_section_configure', {}, 'widget')}
  >
    <WidgetForm level={5} name={selectors.STORE_NAME} />

    <Button
      className="modal-btn btn"
      type={CALLBACK_BUTTON}
      primary={true}
      disabled={!props.saveEnabled}
      label={trans('add', {}, 'actions')}
      callback={() => {
        props.create(props.widget)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModalComponent.propTypes = {
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  create: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const ParametersModal = connect(
  (state) => ({
    saveEnabled: selectors.saveEnabled(state),
    widget: selectors.widget(state)
  })
)(ParametersModalComponent)

export {
  MODAL_WIDGET_CREATION_PARAMETERS,
  ParametersModal
}
