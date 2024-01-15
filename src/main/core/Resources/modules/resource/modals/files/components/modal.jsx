import cloneDeep from 'lodash/cloneDeep'
import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DataInput} from '#/main/app/data/components/input'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const Files = props =>
  <ul>
    {Object.keys(props.files).map(key =>
      <li key={key}>
        {props.files[key].name}
        <Button
          className="btn btn-text-danger btn-sm"
          dangerous={true}
          icon="fa fa-fw fa-trash"
          label={trans('delete', {}, 'actions')}
          callback={() => props.onRemove(key)}
          tooltip="left"
        />
      </li>
    )}
  </ul>

Files.propTypes = {
  files: T.object.isRequired,
  onRemove: T.func.isRequired
}

class ResourceFilesCreationModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      files: {}
    }
    this.removeFile = this.removeFile.bind(this)
  }

  removeFile(id) {
    const files = cloneDeep(this.state.files)
    delete files[id]
    this.setState({files: files})
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'parent', 'add', 'createFiles')}
        icon="fa fa-fw fa-file-upload"
        title={trans('add_files', {}, 'resource')}
      >
        <div className="modal-body">
          {0 < Object.keys(this.state.files).length &&
            <Files
              files={this.state.files}
              onRemove={this.removeFile}
            />
          }

          <DataInput
            id="add-resource-files"
            type="file"
            label={trans('file')}
            multiple={true}
            autoUpload={false}
            onChange={(files) => {
              const newFiles = cloneDeep(this.state.files)
              Object.values(files).forEach(file => {
                const id = makeId()
                newFiles[id] = file
              })
              this.setState({files: newFiles})
            }}
          />
        </div>

        <Button
          className="modal-btn"
          variant="btn"
          size="lg"
          type={CALLBACK_BUTTON}
          primary={true}
          label={trans('create', {}, 'actions')}
          disabled={0 === Object.keys(this.state.files).length}
          callback={() => this.props.createFiles(this.props.parent, Object.values(this.state.files), (newNodes) => {
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