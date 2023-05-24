import React from 'react'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'
import {ColorChart} from '#/main/theme/color/components/color-chart'

const ColorDot = ( props ) => {
  return (
    <Button
      type={MENU_BUTTON}
      icon="fa fa-fw"
      className="color-dot"
      style={{ backgroundColor: props.value }}
      opened={props.opened}
      onClick={props.onClick}
      menu={
        <div className="dropdown-menu">
          <ColorChart
            selected={props.value}
            onChange={props.onChange}
          />
        </div>
      }
    >
      {props.children}
    </Button>
  )
}

const ColorPalette = props => {
  let current = null
  let colors = props.formData.colors || []

  if( props.formData.colors && props.formData.colors.length > 0 ) {
    current = props.formData.colors.map((color, index) => {
      return (
        <div className="color-dot-config" key={index}>
          <ColorDot
            id={`color-${index}`}
            colorIcon="fa fa-fw"
            hideInput={props.hideInput}
            onChange={(color) => props.updateProp('colors[' + index + ']', color)}
            onClick={() => props.updateProp('openedIndex', props.formData.openedIndex === index ? -1 : index ) }
            opened={props.formData.openedIndex === index}
            value={color}
          />
          <Button
            className="btn"
            size="xs"
            tooltip="bottom"
            label={trans('delete', {}, 'actions')}
            dangerous={true}
            type={CALLBACK_BUTTON}
            callback={() => props.updateProp('colors', props.formData.colors.filter((c, i) => i !== index))}
            icon="fa fa-fw fa-trash"
          />
        </div>
      )
    })
  }

  return (
    <div className="color-dot-list">
      {current}
      <Button
        type={CALLBACK_BUTTON}
        id={'new-color'}
        className="color-dot"
        callback={() => {
          props.updateProp('colors[' + colors.length + ']', '#FFFFFF')
          props.updateProp('openedIndex', colors.length)
        }}
      >
        <span className="fa fa-fw fa-plus"></span>
      </Button>
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
