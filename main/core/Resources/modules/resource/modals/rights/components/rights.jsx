import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans}  from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

import {selectors} from '#/main/core/resource/modals/rights/store'

import {ResourceRights} from '#/main/core/resource/components/rights'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const RightsModalComponent = props =>
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

    <Button
      className="btn modal-btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.nodeForm, props.updateNode)
        props.fadeModal()
      }}
    />
  </Modal>

RightsModalComponent.propTypes = {
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
  fadeModal: T.func.isRequired
}

const RightsModal = connect(
  (state) => ({
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME)),
    nodeForm: formSelect.data(formSelect.form(state, selectors.STORE_NAME))
  }),
  (dispatch) => ({
    updateRights(perms) {
      dispatch(formActions.updateProp(selectors.STORE_NAME, 'rights', perms))
    },
    loadNode(resourceNode) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, resourceNode))
    },
    save(resourceNode, update) {
      dispatch(formActions.saveForm(selectors.STORE_NAME, ['claro_resource_action', {
        resourceType: resourceNode.meta.type,
        action: 'rights',
        id: resourceNode.id
      }])).then((response) => update(response))
    }
  })
)(RightsModalComponent)

export {
  RightsModal
}
