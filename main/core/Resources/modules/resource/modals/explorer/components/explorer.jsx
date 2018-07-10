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
import {selectors} from '#/main/core/resource/modals/explorer/store'

const ExplorerModalComponent = props =>
  <Modal
    {...omit(props, 'current', 'primaryAction', 'actions', 'confirmText', 'selected', 'handleSelect')}
    subtitle={props.current && props.current.name}
    onEntering={() => props.initialize(props.root)}
  >
    <ResourceExplorer
      name={selectors.STORE_NAME}
      primaryAction={props.primaryAction}
      actions={props.actions}
    />

    <Button
      className="modal-btn btn"
      type="callback"
      primary={true}
      label={props.confirmText}
      callback={() => {
        props.fadeModal()
        props.handleSelect(props.selected)
      }}
    />
  </Modal>

ExplorerModalComponent.propTypes = {
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  primaryAction: T.func,
  actions: T.oneOfType([T.array, T.object]),
  confirmText: T.string,
  selected: T.array.isRequired,
  handleSelect: T.func.isRequired,
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
  (dispatch, ownProps) => ({
    initialize(root) {
      dispatch(actions.initialize(selectors.STORE_NAME, root, ownProps.current))
    }
  })
)(ExplorerModalComponent)

export {
  ExplorerModal
}
