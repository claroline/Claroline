import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/example/tools/example/modals/parameters/store'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const ParametersModal = (props) =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    onEntering={() => props.reset()}
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
              label: trans('name'),
              type: 'string',
              required: true
            }, {
              name: 'type',
              label: trans('type'),
              type: 'choice',
              options: {
                choices: {
                  text: trans('text'),
                  image: trans('image'),
                  video: trans('video')
                }
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-info-circle',
          title: trans('information'),
          fields: [
            {
              name: 'meta.description',
              label: trans('description'),
              type: 'html'
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        htmlType="submit"
        disabled={!props.saveEnabled}
        callback={() => {
          props.save(props.formData, props.fadeModal)
        }}
      />
    </FormData>
  </Modal>

ParametersModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  reset: T.func.isRequired,

  // from modals
  fadeModal: T.func
}

export {
  ParametersModal
}
