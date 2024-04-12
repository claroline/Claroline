import {connect} from 'react-redux'
import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'

import omit from 'lodash/omit'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import {trans} from '#/main/app/intl/translation'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DataInput} from '#/main/app/data/components/input'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {actions} from '#/plugin/cursus/modals/presence/evidences/store'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

const EvidenceModalComponent = (props) => {
  const [files, setFiles] = useState([])

  return (
    <Modal
      {...omit(props, 'parent', 'add', 'editable', 'createFiles')}
      icon={classes('fa fa-fw', {
        'fa-file-upload': props.editable,
        'fa-file-lines': !props.editable
      })}
      title={trans(props.editable ? 'add_evidences' : 'evidences', {}, 'presence')}
    >
      <div className="modal-body">
        {props.editable &&
          <DataInput
            id="add-evidences-files"
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
        }

        {!props.editable && props.parent.evidences.map((evidence, key) =>
          <FileThumbnail
            key={key}
            file={evidence}
            download={{url: ['apiv2_cursus_presence_evidence_download', {id: props.parent.id, file: evidence}]}}
          />
        )}
      </div>

      {props.editable &&
        <Button
          className="modal-btn"
          variant="btn"
          size="lg"
          type={CALLBACK_BUTTON}
          primary={true}
          label={trans('add', {}, 'actions')}
          disabled={isEmpty(files)}
          callback={() => props.createFiles(props.parent, files, () => {
            props.onSuccess()
            props.fadeModal()
          })}
        />
      }
    </Modal>
  )
}

EvidenceModalComponent.propTypes = {
  parent: T.object.isRequired,
  editable: T.bool.isRequired,
  onSuccess: T.func.isRequired,
  createFiles: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const EvidenceModal = connect(
  null,
  (dispatch) => ({
    createFiles(parent, files, callback) {
      dispatch(actions.createFiles(parent, files, callback))
    }
  })
)(EvidenceModalComponent)

export {
  EvidenceModal
}
