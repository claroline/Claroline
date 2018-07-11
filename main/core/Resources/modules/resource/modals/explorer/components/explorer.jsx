import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'

import {
  actions,
  selectors as explorerSelectors
} from '#/main/core/resource/explorer/store'
import {selectors} from '#/main/core/resource/explorer/store'

const ExplorerModalComponent = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'current', 'primaryAction', 'actions', 'confirmText', 'selected', 'selectAction')}
      subtitle={props.current && props.current.name}
      onEntering={() => props.initialize(props.root, props.current)}
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

ExplorerModalComponent.propTypes = {
  root: T.shape(
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
  initialize: T.func.isRequired,
  fadeModal: T.func.isRequired
}

ExplorerModalComponent.defaultProps = {
  icon: 'fa fa-fw fa-folder',
  title: trans('resource_explorer', {}, 'resource'),
  confirmText: trans('select', {}, 'actions')
}

const ExplorerModal = connect(
  (state) => ({
    current: explorerSelectors.current(explorerSelectors.explorer(state, selectors.STORE_NAME)),
    selected: explorerSelectors.selectedFull(explorerSelectors.explorer(state, selectors.STORE_NAME))
  }),
  (dispatch) => ({
    initialize(root, current) {
      dispatch(actions.initialize(selectors.STORE_NAME, root, current))
    }
  })
)(ExplorerModalComponent)

export {
  ExplorerModal
}
