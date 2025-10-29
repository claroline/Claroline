import React, {useCallback, useRef, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import CloseButton from 'react-bootstrap/CloseButton'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {ModalEmpty} from '#/main/app/overlays/modal/components/empty'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {Poster} from '#/main/app/components/poster'
import {actions} from '#/main/app/api/store'
import {useDispatch} from 'react-redux'
import {FileDrop} from '#/main/app/overlays/dnd/components/file-drop'
import {Alert} from '#/main/app/components/alert'

const ImageEditorModal = (props) => {
  const dispatch = useDispatch()

  const inputRef = useRef(null)
  const [url, setUrl] = useState(props.url)
  const [uploadedFiles, setUploadedFiles] = useState([])
  const [error, setError] = useState(null)

  const onFileSelect = useCallback((e) => {
    if (!isEmpty(e.target.files)) {
      uploadFile(e.target.files)
    }
  }, [props.uploadUrl])

  const uploadFile = useCallback((files) => {
    const file = files[0]
    if (file) {
      dispatch(actions.uploadFile(file, props.uploadUrl)).then(
        (response) => {
          const fileUrl = Array.isArray(response) ? response[0].url : response.url
          setUrl(fileUrl)
          setUploadedFiles([].concat(uploadedFiles, [fileUrl]))
        },
        () => setError(trans('invalid_image', {}, 'validators'))
      )
    }
  }, [props.uploadUrl])

  return (
    <ModalEmpty
      {...omit(props, 'uploadUrl', 'url', 'onChange')}
      centered={true}
      scrollable={true}
    >
      <div className="modal-header">
        <CloseButton onClick={props.fadeModal} aria-label={trans('close', {}, 'actions')} />
      </div>

      <div className="modal-body pt-0">
        <input
          type="file"
          className="visually-hidden"
          accept="image"
          multiple={false}
          ref={inputRef}
          onChange={onFileSelect}
        />

        <FileDrop
          className="rounded-3"
          accept={['image/*']}
          onDrop={uploadFile}
          size="sm"
        >
          {url ?
            <Poster url={url} className="rounded-3 z-0" /> :
            <div className="bg-body-tertiary rounded-3 ratio ratio-poster">
              <div role="presentation" className="p-3 d-flex flex-column align-items-center justify-content-center gap-3">
                <span className="fa fa-camera fs-1 text-body-tertiary" />
                Aucune image de couverture
              </div>
            </div>
          }
        </FileDrop>

        <div className="fs-sm mt-2 text-body-secondary d-flex align-items-center gap-2">
          <b>Taille maximale :</b> 4mo
          <span role="presentation">-</span>
          <b>Format :</b> 1:6
          <span role="presentation">-</span>
          <b>Taille recommandée :</b> 1920 x 320px
        </div>

        {error &&
          <Alert type="danger" className="mt-3">{error}</Alert>
        }

        {(props.url || url || !isEmpty(uploadedFiles)) &&
          <>
            <hr aria-hidden={true} className="my-4" />
            <div className="posters-grid">
              {uploadedFiles.map(file =>
                <Button
                  key={file}
                  type={CALLBACK_BUTTON}
                  className={classes('rounded-2 btn-poster', {
                    'selected': file === url
                  })}
                  //label={trans('Image actuelle')}
                  tooltip="top"
                  callback={() => setUrl(file)}
                >
                  <Poster url={file} className="rounded-2 z-0" />
                </Button>
              )}

              {props.url &&
                <Button
                  type={CALLBACK_BUTTON}
                  className={classes('rounded-2 btn-poster', {
                    'selected': props.url === url
                  })}
                  label={trans('Image actuelle')}
                  tooltip="top"
                  callback={() => setUrl(props.url)}
                >
                  <Poster url={props.url} className="rounded-2 z-0" />
                </Button>
              }

              <Button
                type={CALLBACK_BUTTON}
                className={classes('rounded-2 btn-poster', {
                  'selected': null === url
                })}
                label={trans('Aucune image de couverture')}
                tooltip="top"
                callback={() => setUrl(null)}
              >
                <div className="bg-body-tertiary rounded-2 ratio ratio-poster" aria-hidden={true}>
                  <div className=" d-flex flex-column align-items-center justify-content-center">
                    <span className="fa fa-ban fs-4 text-body-tertiary" />
                  </div>
                </div>
              </Button>
            </div>
          </>
        }
      </div>

      <div className="modal-footer">
        <Button
          className="btn btn-link"
          type={CALLBACK_BUTTON}
          label={trans('upload_file', {}, 'actions')}
          callback={() => inputRef.current.click()}
        />

        <Button
          className="btn btn-primary"
          type={CALLBACK_BUTTON}
          label={trans('save', {}, 'actions')}
          disabled={url === props.url}
          callback={() => {
            props.onChange(url)
            props.fadeModal()
          }}
        />
      </div>
    </ModalEmpty>
  )
}

ImageEditorModal.propTypes = {
  uploadUrl: T.oneOfType([T.array, T.string]).isRequired,
  url: T.string,
  onChange: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ImageEditorModal
}
