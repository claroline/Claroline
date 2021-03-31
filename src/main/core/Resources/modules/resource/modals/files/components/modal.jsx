import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DataInput} from '#/main/app/data/components/input'

import {trans} from '#/main/app/intl/translation'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

class ResourceFilesCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      files: []
    }
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'parent', 'add', 'createFiles')}
        icon="fa fa-fw fa-file-upload"
        title={trans('add_files', {}, 'resource')}
      >
        <div className="modal-body">
          <DataInput
            id="files"
            label={trans('files')}
            type="file"
            required={true}
            options={{
              multiple: true,
              autoUpload: false
            }}
            value={this.state.files}
            onChange={(files) => this.setState({files: files})}
          />
        </div>

        <Button
          className="modal-btn btn"
          type={CALLBACK_BUTTON}
          primary={true}
          label={trans('create', {}, 'actions')}
          disabled={!this.state.files || 0 === this.state.files.length}
          callback={() => this.props.createFiles(this.props.parent, this.state.files, (newNodes) => {
            this.props.add(newNodes)
            this.props.fadeModal()
          })}
        />
      </Modal>
    )
  }
}

ResourceFilesCreationModal.propTypes = {
  parent: T.shape(ResourceNodeTypes.propTypes).isRequired,
  add: T.func.isRequired,
  createFiles: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ResourceFilesCreationModal
}