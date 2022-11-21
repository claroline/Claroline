import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/theme/administration/appearance/modals/icon-set-creation/store/selectors'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const IconSetCreationModal = props =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save', 'reset', 'updateProp', 'onSave')}
    icon="fa fa-fw fa-plus"
    title={trans('new_icon_set', {}, 'appearance')}
    onExiting={() => props.reset()}
  >
    <FormData
      name={selectors.STORE_NAME}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'archive',
              type: 'file',
              label: trans('archive'),
              required: true,
              onChange: (file) => {
                if (!props.formData.name) {
                  const fileParts = file.name.split('.')
                  props.updateProp('name', fileParts[0])
                }
              },
              options: {
                autoUpload: false
              }
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn btn-primary"
        type={CALLBACK_BUTTON}
        htmlType="submit"
        primary={true}
        label={trans('create', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => props.save(props.formData).then((response) => {
          props.fadeModal()

          if (props.onSave) {
            props.onSave(response)
          }
        })}
      />
    </FormData>
  </Modal>

IconSetCreationModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  formData: T.object,
  onSave: T.func,
  updateProp: T.func.isRequired,
  save: T.func.isRequired,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  IconSetCreationModal
}
