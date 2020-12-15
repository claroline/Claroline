import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/plugin/home/tools/home/editor/modals/parameters/store'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {TabForm} from '#/plugin/home/tools/home/editor/components/form'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'saveEnabled', 'update', 'setErrors', 'currentContext', 'administration', 'save', 'tab', 'loadTab', 'formData')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.tab.longTitle}
    onEntering={() => props.loadTab(props.tab)}
  >
    <TabForm
      level={5}
      name={selectors.STORE_NAME}
      update={props.update}
      setErrors={props.setErrors}

      currentTab={props.formData}
      currentContext={props.currentContext}
      administration={props.administration}
    >
      <Button
        className="modal-btn btn"
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
    </TabForm>
  </Modal>

ParametersModal.propTypes = {
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  administration: T.bool.isRequired,
  tab: T.shape(
    TabTypes.propTypes
  ).isRequired,
  formData: T.shape(
    TabTypes.propTypes
  ).isRequired,
  loadTab: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  update: T.func.isRequired,
  setErrors: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
