import {connect} from 'react-redux'
import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'

import omit from 'lodash/omit'
import classes from 'classnames'
import isNull from 'lodash/isNull'
import {trans} from '#/main/app/intl/translation'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DataInput} from '#/main/app/data/components/input'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {actions} from '#/plugin/cursus/modals/presence/evidences/store'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

const EvidenceModalComponent = (props) => {
  const [file, setFile] = useState(null)

  return (
    <Modal
      {...omit(props, 'parent', 'add', 'editable', 'createFile', 'onSuccess')}
      icon={classes('fa fa-fw', {
        'fa-file-upload': props.editable,
        'fa-file-lines': !props.editable
      })}
      title={trans(props.editable ? 'add_evidence' : 'evidence', {}, 'presence')}
    >
      <div className="modal-body">
        {props.editable &&
          <DataInput
            id="add-evidence-file"
            type="file"
            label={trans('files')}
            value={file}
            onChange={setFile}
            required={true}
            options={{
              multiple: false,
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
          disabled={isNull(file)}
          callback={() => props.createFile(props.parent, file, () => {
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
  createFile: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const EvidenceModal = connect(
  null,
  (dispatch) => ({
    createFile(parent, file, callback) {
      dispatch(actions.createFile(parent, file, callback))
    }
  })
)(EvidenceModalComponent)

export {
  EvidenceModal
}
