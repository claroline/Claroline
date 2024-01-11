import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {selectors} from '#/main/privacy/administration/privacy/store'
import get from 'lodash/get'

const EditorModal = props =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-file-shield"
    title={trans('terms_of_service',{},'privacy')}
    size="lg"
  >
    <FormData
      level={5}
      flush={true}
      name={selectors.FORM_NAME}
      definition={[
        {
          title: trans('terms_of_service', {}, 'privacy'),
          fields: [
            {
              name: 'tos.enabled',
              type: 'boolean',
              label: trans('terms_of_service_activation', {}, 'privacy'),
              help: trans('terms_of_service_activation_help', {}, 'privacy'),
              linked: [
                {
                  name: 'tos.template',
                  label: trans('terms_of_service', {}, 'template'),
                  type: 'template',
                  displayed: get(props, 'tos.enabled'),
                  options: {
                    picker: {
                      filters: [{
                        property: 'typeName',
                        value: 'terms_of_service',
                        locked: true
                      }]
                    }
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={CALLBACK_BUTTON}
      label={trans('save', {}, 'actions')}
      callback={() => {
        props.save(props.formData)
        props.fadeModal()
      }}
      primary={true}
    />
  </Modal>

EditorModal.propTypes = {
  save: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  fadeModal: T.func.isRequired,
  formData: T.object.isRequired
}

export {
  EditorModal
}
