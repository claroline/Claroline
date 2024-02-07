import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DataInput} from '#/main/app/data/components/input'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const ResourceFilesCreationModal = (props) => {
  const [files, setFiles] = useState([])

  return (
    <Modal
      {...omit(props, 'parent', 'add', 'createFiles')}
      icon="fa fa-fw fa-file-upload"
      title={trans('add_files', {}, 'resource')}
    >
      <div className="modal-body">
        <DataInput
          id="add-resource-files"
          type="file"
          label={trans('files')}
          value={files}
          onChange={setFiles}
          required={true}
          options={{
            multiple: true,
            autoUpload: false
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
        disabled={isEmpty(files)}
        callback={() => props.createFiles(props.parent, files, (newNodes) => {
          props.add(newNodes)
          props.fadeModal()
        })}
      />
    </Modal>
  )
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