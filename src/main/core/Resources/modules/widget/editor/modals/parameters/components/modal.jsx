import React, {useCallback} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/widget/editor/modals/parameters/store'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {WidgetForm} from '#/main/core/widget/editor/components/form'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'save', 'widget', 'loadWidget', 'formData')}
    className="home-section-parameters"
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.widget.name}
    onEntering={() => props.loadWidget(props.widget)}
    size="lg"
  >
    <WidgetForm
      level={5}
      name={selectors.STORE_NAME}
    >
      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        htmlType="submit"
        callback={() => {
          props.save(props.formData)
          props.fadeModal()
        }}
      />
    </WidgetForm>
  </Modal>

ParametersModal.propTypes = {
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

export {
  ParametersModal
}
