import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'
import {ColorChart} from '#/main/theme/color/containers/color-chart'

const ColorDot = ( props ) => {
  return (
    <Button
      type={MENU_BUTTON}
      className="color-dot lg"
      style={{ backgroundColor: props.value }}
      opened={props.opened}
      onClick={props.onClick}
      menu={
        <div className="dropdown-menu">
          <ColorChart
            view={'selector'}
            noLibrary={true}
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

ColorDot.propTypes = {
  value: T.string,
  opened: T.bool,
  onClick: T.func,
  onChange: T.func,
  children: T.node
}

const ColorPalette = ({ formData: { colors = [], openedIndex }, hideInput, updateProp }) => {
  let current = null

  if( colors && colors.length > 0 ) {
    current = colors.map((color, index) => {
      return (
        <div className="color-dot-config" key={index}>
          <ColorDot
            id={`color-${index}`}
            hideInput={hideInput}
            onChange={(color) => updateProp('colors[' + index + ']', color)}
            onClick={() => updateProp('openedIndex', openedIndex === index ? -1 : index ) }
            opened={openedIndex === index}
            value={color}
          />
          <Button
            className="btn"
            size="xs"
            tooltip="bottom"
            label={trans('delete', {}, 'actions')}
            dangerous={true}
            type={CALLBACK_BUTTON}
            callback={() => updateProp('colors', colors.filter((c, i) => i !== index))}
            icon="fa fa-fw fa-trash"
          />
        </div>
      )
    })
  }

  return (
    <div className="color-dot-list color-chart-library">
      {current}
      <Button
        type={CALLBACK_BUTTON}
        id={'new-color'}
        className="color-dot-lg"
        callback={() => {
          updateProp('colors[' + colors.length + ']', '#FFFFFF')
          updateProp('openedIndex', colors.length)
        }}
      >
        <span className="fa fa-fw fa-plus"></span>
      </Button>
    </div>
  )
}

ColorPalette.propTypes = {
  formData: T.shape({
    colors: T.arrayOf(T.string),
    openedIndex: T.number
  }),
  updateProp: T.func,
  hideInput: T.bool
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
          formData={props.formData}
          updateProp={props.updateProp}
        />

        <Button
          className="modal-btn btn"
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

ColorChartParametersModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  formData: T.shape({
    colors: T.arrayOf(T.string),
    openedIndex: T.number
  }),
  onSave: T.func,
  updateProp: T.func.isRequired,
  save: T.func.isRequired,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired,
  colorChart: T.object
}

export {
  ColorChartParametersModal
}
