import React from 'react'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'
import {ColorInput} from '#/main/theme/data/types/color/components/input'

const ColorPalette = props => {
  let current = null
  let colors = props.formData.colors || []

  if( props.formData.colors && props.formData.colors.length > 0 ) {
    current = props.formData.colors.map((color, index) => {
      return (
        <div>
          <ColorInput
            id={`color-${index}`}
            className="color"
            colorIcon="fa fa-fw"
            hideInput={props.hideInput}
            onChange={color => props.updateProp('colors[' + index + ']', color)}
            value={color}
            size="md"
          />
          <Button
            className="btn btn-link btn-md danger color-chart-button"
            type={CALLBACK_BUTTON}
            onClick={() => props.updateProp('colors', props.formData.colors.filter((c, i) => i !== index))}
            icon="fa fa-fw fa-trash"
          />
        </div>
      )
    })
  }

  return (
    <div className="list-group color-chart-colors-list-group">
      {current}
      <ColorInput
        id={`new-color`}
        colorIcon="fa fa-fw fa-plus"
        onChange={color => props.updateProp('colors['+(colors.length)+']', color)}
        hideInput={true}
        size="md"
      />
    </div>
  )
}

const ColorChartParametersModal = props => {
  return (
    <Modal
      {...omit(props, 'formData', 'saveEnabled', 'save', 'reset', 'updateProp', 'onSave')}
      icon={props.colorChart ? 'fa fa-fw fa-pencil' : 'fa fa-fw fa-plus'}
      title={props.colorChart ? trans('edit_color_chart', {}, 'appearance') : trans('new_color_chart', {}, 'appearance')}
      onEntering={() => {
        props.reset(props.colorChart)
      }}
      onExited={props.reset}
    >
      <FormData
        name={selectors.STORE_NAME}
        definition={[{
          title: trans('general'),
          primary: true,
          fields: [{
            name: 'name',
            label: trans('name'),
            type: 'string',
            required: true
          }]
        }]}
      >
        <ColorPalette
          hideInput={true}
          {...props}
        />

        <Button
          className="modal-btn btn btn-primary"
          type={CALLBACK_BUTTON}
          primary={true}
          htmlType="submit"
          label={trans('save', {}, 'actions')}
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
  )
}

export {
  ColorChartParametersModal
}
