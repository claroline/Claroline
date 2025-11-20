import React from 'react'
import {useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {ModalButton} from '#/main/app/buttons'

import {actions} from '#/main/app/api/store'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Poster} from '#/main/app/components/poster'
import {MODAL_IMAGE_EDITOR} from '#/main/theme/modals/image'
import {FileDrop} from '#/main/app/overlays/dnd/components/file-drop'

const PosterInput = (props) => {
  const dispatch = useDispatch()

  const uploadFile = (files) => {
    const file = files[0]
    if (file) {
      props.uploadFile(file, props.uploadUrl, props.onChange, props.onError)
      dispatch(actions.uploadFile(file, props.uploadUrl)).then(
        (response) => props.onChange(Array.isArray(response) ? response[0].url : response.url),
        () => props.onError(trans('invalid_image', {}, 'validators'))
      )
    }
  }

  return (
    <FileDrop
      className="rounded-3"
      accept={['image/*']}
      disabled={props.disabled}
      onDrop={uploadFile}
      size="sm"
    >
      <ModalButton
        className="rounded-3 focus-ring z-0"
        disabled={props.disabled}
        modal={[MODAL_IMAGE_EDITOR, {
          uploadUrl: props.uploadUrl,
          url: props.value,
          onChange: props.onChange
        }]}
      >
        {props.value ?
          <Poster url={props.value} className="poster-preview rounded-3" /> :
          <div className="poster-placeholder bg-body-tertiary rounded-3 ratio ratio-poster">
            <div role="presentation" className="p-3 d-flex flex-column align-items-center justify-content-center gap-3 ">
              <span className="fa fa-camera fs-1 text-body-tertiary" />
              <b className="fs-sm text-body-secondary">Cliquez pour ajouter une image de couverture</b>
            </div>
          </div>
        }
      </ModalButton>
    </FileDrop>
  )
}

implementPropTypes(PosterInput, DataInputTypes, {
  value: T.string, // the url of the image
  uploadUrl: T.array.isRequired
}, {
  uploadUrl: ['apiv2_public_file_image_upload']
})

export {
  PosterInput
}
