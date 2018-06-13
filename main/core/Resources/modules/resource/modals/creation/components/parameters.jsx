import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ContentMeta} from '#/main/app/content/meta/components/meta'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_RESOURCE_CREATION_RIGHTS} from '#/main/core/resource/modals/creation/components/rights'

import {actions, selectors} from '#/main/core/resource/modals/creation/store'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceForm} from '#/main/core/resource/components/form'

/*import {ShortcutCreation} from '#/plugin/link/resources/shortcut/components/creation'
<ShortcutCreation />*/

const MODAL_RESOURCE_CREATION_PARAMETERS = 'MODAL_RESOURCE_CREATION_PARAMETERS'

const ParametersModalComponent = props =>
  <Modal
    {...omit(props, 'parent', 'newNode', 'saveEnabled', 'save', 'configureRights')}
    icon="fa fa-fw fa-plus"
    title={trans('new_resource', {}, 'resource')}
    subtitle="2. Configurer la ressource"
  >
    <ContentMeta meta={props.newNode.meta} />

    <ResourceForm level={5} meta={false} name={selectors.FORM_NAME} dataPart="resourceNode" />

    <Button
      className="modal-btn btn-link"
      type="callback"
      label={trans('edit-rights', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={props.configureRights}
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

ParametersModalComponent.propTypes = {
  parent: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  newNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  configureRights: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const ParametersModal = connect(
  (state) => ({
    parent: selectors.parent(state),
    newNode: selectors.newNode(state),
    saveEnabled: selectors.saveEnabled(state)
  }),
  (dispatch) => ({
    save(parent, close) {
      dispatch(actions.create(parent)).then(close)
    },
    configureRights() {
      dispatch(modalActions.showModal(MODAL_RESOURCE_CREATION_RIGHTS, {}))
    }
  })
)(ParametersModalComponent)

export {
  MODAL_RESOURCE_CREATION_PARAMETERS,
  ParametersModal
}
