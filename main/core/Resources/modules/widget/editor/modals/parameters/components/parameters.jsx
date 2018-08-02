import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/core/widget/editor/modals/parameters/store'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetForm} from '#/main/core/widget/editor/components/form'

const ParametersModalComponent = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'save', 'widget', 'loadWidget', 'formData')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.widget.name}
    onEntering={() => props.loadWidget(props.widget)}
  >
    <WidgetForm
      level={5}
      name={selectors.STORE_NAME}
    />

    <Button
      className="modal-btn btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.formData)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModalComponent.propTypes = {
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  formData: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  loadWidget: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const ParametersModal = connect(
  (state) => ({
    formData: selectors.data(state),
    saveEnabled: selectors.saveEnabled(state)
  }),
  (dispatch) => ({
    loadWidget(widget) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, widget))
    }
  })
)(ParametersModalComponent)

export {
  ParametersModal
}
