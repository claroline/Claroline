/**
 * File upload modal.
 * Displays a modal to allow user to upload files directly in TInyMCE editors.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {UploadModal} from '#/main/app/input/tinymce/modals/upload/containers/modal'

const MODAL_TINYMCE_UPLOAD = 'MODAL_TINYMCE_UPLOAD'

// make the modal available for use
registry.add(MODAL_TINYMCE_UPLOAD, UploadModal)

export {
  MODAL_TINYMCE_UPLOAD
}
