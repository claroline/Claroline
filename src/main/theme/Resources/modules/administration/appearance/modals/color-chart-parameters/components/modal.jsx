import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Menu} from '#/main/app/overlays/menu'
import {FormData} from '#/main/app/content/form/containers/data'

import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'
import {ColorChart} from '#/main/theme/color/containers/color-chart'

const ColorMenu = forwardRef((props, ref) =>
  <div {...omit(props, 'value', 'onChange', 'show', 'close')} ref={ref}>
    <ColorChart
      view="selector"
      noLibrary={true}
      selected={props.value}
      onChange={props.onChange}
    />
  </div>
)

const ColorDot = ( props ) => {
  return (
    <Button
      type={MENU_BUTTON}
      className="color-dot lg"
      label={props.value}
      tooltip="bottom"
      style={{ backgroundColor: props.value }}
      opened={props.opened}
      onToggle={props.onClick}
      menu={
        <Menu
          as={ColorMenu}
          value={props.value}
          onChange={props.onChange}
        />
      }
    >
      {props.children}
    </Button>
  )
}

ColorDot.propTypes = {
  value: T.string,
  opened: T.bool,
  onToggle: T.func,
  onClick: T.func,
  onChange: T.func,
  children: T.node
}

const ColorPalette = ({ formData: { colors = [], openedIndex }, updateProp }) =>
  <>
    <div className="color-dot-list color-chart-library">
      {colors && colors.map((color, index) =>
        <div className="color-dot-config" key={index}>
          <ColorDot
            id={`color-${index}`}
            onChange={(color) => updateProp('colors[' + index + ']', color)}
            onClick={() => updateProp('openedIndex', openedIndex === index ? -1 : index ) }
            opened={openedIndex === index}
            value={color}
          />
          <Button
            variant="btn"
            size="sm"
            tooltip="bottom"
            label={trans('delete', {}, 'actions')}
            dangerous={true}
            type={CALLBACK_BUTTON}
            callback={() => updateProp('colors', colors.filter((c, i) => i !== index))}
            icon="fa fa-fw fa-trash"
          />
        </div>
      )}
    </div>

    <div className="modal-body">
      <Button
        type={CALLBACK_BUTTON}
        id="new-color"
        className="btn btn-outline-primary w-100"
        label={trans('add_color', {}, 'appearance')}
        callback={() => {
          updateProp('colors[' + colors.length + ']', '#FFFFFF')
          updateProp('openedIndex', colors.length)
        }}
      />
    </div>
  </>

ColorPalette.propTypes = {
  formData: T.shape({
    colors: T.arrayOf(T.string),
    openedIndex: T.number
  }),
  updateProp: T.func
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
        flush={true}
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
          className="modal-btn"
          variant="btn"
          size="lg"
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
