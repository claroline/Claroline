import React from 'react'
import get from 'lodash/get'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {Modal} from '#/main/app/overlays/modal/components/modal'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'
import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {actions} from '#/plugin/drop-zone/plugin/configuration/actions'

const CompilatioFormModal = props =>
  <Modal {...props}>
    <div className="modal-body">
      {props.toolForm.data ?
        <form>
          <TextGroup
            id="tool-name"
            label={trans('name', {}, 'platform')}
            value={props.toolForm.data.name}
            onChange={value => props.updateToolForm('name', value)}
            warnOnly={!props.toolForm.validating}
            error={get(props.toolForm.errors, 'name')}
          />
          <TextGroup
            id="tool-url"
            label={trans('url', {}, 'dropzone')}
            value={props.toolForm.data.data.url || ''}
            onChange={value => props.updateToolForm('data.url', value)}
            warnOnly={!props.toolForm.validating}
            error={get(props.toolForm.errors, 'url')}
          />
          <TextGroup
            id="tool-key"
            label={trans('key', {}, 'dropzone')}
            value={props.toolForm.data.data.key || ''}
            onChange={value => props.updateToolForm('data.key', value)}
            warnOnly={!props.toolForm.validating}
            error={get(props.toolForm.errors, 'key')}
          />
        </form> :
        <span className="fa fa-fw fa-circle-o-notch fa-spin"></span>
      }
    </div>
    <div className="modal-footer">
      <button
        className="btn btn-default"
        onClick={() => {
          props.hideModal()
          props.resetToolForm()
        }}
      >
        {trans('cancel', {}, 'actions')}
      </button>
      <button
        className="btn btn-primary"
        onClick={() => {
          props.submitTool(props.toolForm.data)
        }}
      >
        {trans('ok', {}, 'platform')}
      </button>
    </div>
  </Modal>

CompilatioFormModal.propTypes = {
  toolForm: T.object,
  updateToolForm: T.func.isRequired,
  resetToolForm: T.func.isRequired,
  submitTool: T.func.isRequired,
  hideModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    toolForm: state.toolForm
  }
}

function mapDispatchToProps(dispatch) {
  return {
    updateToolForm: (property, value) => dispatch(actions.updateToolForm(property, value)),
    resetToolForm: () => dispatch(actions.resetToolForm()),
    submitTool: (tool) => dispatch(actions.submitTool(tool)),
    hideModal: () => dispatch(modalActions.hideModal())
  }
}

const ConnectedCompilatioFormModal = connect(mapStateToProps, mapDispatchToProps)(CompilatioFormModal)

export {ConnectedCompilatioFormModal as CompilatioFormModal}
