import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'

import {selectors} from '#/main/core/resource/modals/explorer/store'

const ExplorerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'current', 'currentDirectory', 'primaryAction', 'actions', 'confirmText', 'selected', 'selectAction', 'initialize', 'filters')}
      subtitle={props.currentDirectory && props.currentDirectory.name}
      onEntering={() => props.initialize(props.root, props.current, props.filters)}
      bsSize="lg"
    >
      <ResourceExplorer
        name={selectors.STORE_NAME}
        primaryAction={props.primaryAction}
        actions={props.actions}
      />

      <Button
        label={props.confirmText}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

ExplorerModal.propTypes = {
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  currentDirectory: T.shape(
    ResourceNodeTypes.propTypes
  ),
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  primaryAction: T.func,
  actions: T.func,
  selectAction: T.func.isRequired, // action generator for the select button
  confirmText: T.string, // todo : deprecated. kept for retro compatibility. Use the selectAction label instead
  selected: T.array.isRequired,
  filters: T.array,
  initialize: T.func.isRequired,
  fadeModal: T.func.isRequired
}

ExplorerModal.defaultProps = {
  icon: 'fa fa-fw fa-folder',
  title: trans('resource_explorer', {}, 'resource'),
  confirmText: trans('select', {}, 'actions'),
  filters: [],
  current: null
}

export {
  ExplorerModal
}
