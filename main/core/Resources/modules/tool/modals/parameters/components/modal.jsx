import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/tool/modals/parameters/store'

const ParametersModal = props =>
  <Modal
    {...omit(props, 'toolName', 'currentContext', 'data', 'saveEnabled', 'onSave', 'save', 'reset')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={trans(props.toolName, {}, 'tools')}
    onEntering={() => props.reset(props.data)}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              label: trans('poster'),
              type: 'image',
              options: {
                ratio: '3:1'
              }
            }, {
              name: 'thumbnail',
              label: trans('thumbnail'),
              type: 'image'
            }, {
              name: 'display.showIcon',
              label: trans('resource_showIcon', {}, 'resource'),
              type: 'boolean'
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn btn-primary"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => {
          props.save(props.toolName, props.currentContext, props.onSave)
          props.fadeModal()
        }}
      />
    </FormData>
  </Modal>

ParametersModal.propTypes = {
  toolName: T.string.isRequired,
  data: T.object,
  currentContext: T.object.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  onSave: T.func,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
