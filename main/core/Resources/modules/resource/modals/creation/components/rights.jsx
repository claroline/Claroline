import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {MODAL_RESOURCE_CREATION_PARAMETERS} from '#/main/core/resource/modals/creation/components/parameters'

import {actions, selectors} from '#/main/core/resource/modals/creation/store'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceRights} from '#/main/core/resource/components/rights'

const MODAL_RESOURCE_CREATION_RIGHTS = 'MODAL_RESOURCE_CREATION_RIGHTS'

const RightsModalComponent = props =>
  <Modal
    {...omit(props, 'parent', 'saveEnabled', 'save', 'updateRights')}
    icon="fa fa-fw fa-plus"
    title={trans('new_resource', {}, 'resource')}
    subtitle={trans('new_resource_configure_rights', {}, 'resource')}
  >
    <ResourceRights
      resourceNode={props.newNode}
      updateRights={props.updateRights}
    />

    <Button
      className="modal-btn btn-link"
      type="modal"
      label={trans('configure', {}, 'actions')}
      modal={[MODAL_RESOURCE_CREATION_PARAMETERS, {}]}
    />

    <Button
      className="modal-btn btn"
      type="callback"
      primary={true}
      label={trans('create', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => props.save(props.parent, props.fadeModal)}
    />
  </Modal>

RightsModalComponent.propTypes = {
  parent: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  newNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  updateRights: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const RightsModal = connect(
  (state) => ({
    newNode: selectors.newNode(state),
    parent: selectors.parent(state),
    saveEnabled: selectors.saveEnabled(state)
  }),
  (dispatch) => ({
    updateRights() {

    },
    save(parent, close) {
      dispatch(actions.create(parent)).then(close)
    }
  })
)(RightsModalComponent)

export {
  MODAL_RESOURCE_CREATION_RIGHTS,
  RightsModal
}
