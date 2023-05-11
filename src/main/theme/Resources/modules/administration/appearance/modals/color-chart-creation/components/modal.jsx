import React from 'react'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-creation/store/selectors'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const ColorChartCreationModal = props =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save', 'reset', 'updateProp', 'onSave')}
    icon="fa fa-fw fa-plus"
    title={trans('new_color_chart', {}, 'appearance')}
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
            },
            {
              name: 'colors.color1',
              label: trans('color 1'),
              type: 'color'
            },
            {
              name: 'colors.color2',
              label: trans('color 2'),
              type: 'color'
            },
            {
              name: 'colors.color3',
              label: trans('color 3'),
              type: 'color'
            },
            {
              name: 'colors.color4',
              label: trans('color 4'),
              type: 'color'
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn btn-primary"
        type={CALLBACK_BUTTON}
        primary={true}
        htmlType="submit"
        label={trans('add', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => {props.save(props.formData, props.fadeModal)}}
      />
    </FormData>
  </Modal>


export {
  ColorChartCreationModal
}
