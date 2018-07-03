import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'

import {selectors} from '#/main/core/resource/modals/parameters/store'
import {ResourceForm} from '#/main/core/resource/components/form'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const ParametersModalComponent = props =>
  <Modal
    {...omit(props, 'resourceNode', 'saveEnabled', 'loadNode', 'save')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.resourceNode.name}
    onEntering={() => props.loadNode(props.resourceNode)}
  >
    <ResourceForm name={selectors.STORE_NAME} />

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

ParametersModalComponent.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  loadNode: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const ParametersModal = connect(
  (state) => ({
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, selectors.STORE_NAME))
  }),
  (dispatch) => ({
    loadNode(resourceNode) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, resourceNode))
    },

    save(resourceNode) {
      dispatch(formActions.saveForm(selectors.STORE_NAME, ['claro_resource_action', {
        resourceType: resourceNode.meta.type,
        action: 'configure',
        id: resourceNode.id
      }]))
    }
  })
)(ParametersModalComponent)

export {
  ParametersModal
}
