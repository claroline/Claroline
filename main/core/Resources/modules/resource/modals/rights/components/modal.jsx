import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox'

import {ResourceRights} from '#/main/core/resource/components/rights'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

// TODO : fix recursive condition

const RightsModal = props =>
  <Modal
    {...omit(props, 'resourceNode', 'saveEnabled', 'save', 'updateRights', 'loadNode', 'updateNode', 'nodeForm')}
    icon="fa fa-fw fa-lock"
    title={trans('rights')}
    subtitle={props.resourceNode.name}
    onEntering={() => {
      props.loadNode(props.resourceNode)
    }}
  >
    {!isEmpty(props.nodeForm.id) &&
      <ResourceRights
        resourceNode={props.nodeForm}
        updateRights={props.updateRights}
      />
    }

    {'directory' === props.resourceNode.meta.type &&
      <div className="modal-body">
        <Checkbox
          id={'recursive-node-' + props.resourceNode.id}
          label={trans('apply_recursively_to_directories', {}, 'platform')}
          checked={props.recursiveEnabled}
          onChange={value => props.setRecursiveEnabled(value)}
        />
      </div>
    }

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.nodeForm, props.updateNode, props.recursiveEnabled)
        props.fadeModal()
      }}
    />
  </Modal>

RightsModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  nodeForm: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  updateRights: T.func.isRequired,
  loadNode: T.func.isRequired,
  updateNode: T.func.isRequired,
  fadeModal: T.func.isRequired,
  setRecursiveEnabled: T.func.isRequired,
  recursiveEnabled: T.bool.isRequired
}

export {
  RightsModal
}
