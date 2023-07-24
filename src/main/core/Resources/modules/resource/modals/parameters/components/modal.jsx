import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/core/resource/modals/parameters/store'
import {ResourceForm} from '#/main/core/resource/components/form'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'resourceNode', 'saveEnabled', 'loadNode', 'updateNode', 'save')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.resourceNode.name}
    onEntering={() => props.loadNode(props.resourceNode)}
    size="lg"
  >
    <ResourceForm
      name={selectors.STORE_NAME}
      flush={true}
    >
      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        htmlType="submit"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => {
          props.save(props.resourceNode, props.updateNode)
          props.fadeModal()
        }}
      />
    </ResourceForm>
  </Modal>

ParametersModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  loadNode: T.func.isRequired,
  updateNode: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
