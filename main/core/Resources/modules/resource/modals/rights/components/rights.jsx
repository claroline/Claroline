import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans}  from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'

import {selectors} from '#/main/core/resource/modals/rights/store'
import {ResourceRights} from '#/main/core/resource/components/rights'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const RightsModalComponent = props =>
  <Modal
    {...omit(props, 'resourceNode', 'saveEnabled', 'save', 'updateRights', 'loadRights')}
    icon="fa fa-fw fa-lock"
    title={trans('rights')}
    subtitle={props.resourceNode.name}
    onEntering={() => props.loadRights(props.resourceNode)}
  >
    <ResourceRights
      resourceNode={props.resourceNode}
      updateRights={() => props.updateRights()}
    />

    <Button
      className="btn modal-btn"
      type="callback"
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.resourceNode)
        props.fadeModal()
      }}
    />
  </Modal>

RightsModalComponent.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  updateRights: T.func.isRequired,
  loadRights: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const RightsModal = connect(
  (state) => ({
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
  }),
  (dispatch) => ({
    updateRights() {

    },
    loadRights(resourceNode) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, resourceNode.rights))
    },
    save(resourceNode) {
      dispatch(formActions.saveForm(selectors.STORE_NAME, ['claro_resource_action', {
        resourceType: resourceNode.meta.type,
        action: 'rights',
        id: resourceNode.id
      }]))
    }
  })
)(RightsModalComponent)

export {
  RightsModal
}
