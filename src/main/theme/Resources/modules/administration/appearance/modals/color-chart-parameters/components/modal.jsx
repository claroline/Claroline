import React, {useState} from 'react'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'

const ColorChartParametersModal = props => {
  const { colorChart } = props
  const [colorCount, setColorCount] = useState(colorChart ? colorChart.colors.length : 1)
  const icon = colorChart ? 'fa fa-fw fa-pencil' : 'fa fa-fw fa-plus'
  const label = colorChart ? trans('edit_color_chart', {}, 'appearance') : trans('new_color_chart', {}, 'appearance')

  const addColor = () => {
    setColorCount(colorCount + 1)
  }

  const colorFields = Array.from({ length: colorCount }, (_, i) => ({
    name: `colors.color${i + 1}`,
    label: trans(`color ${i + 1}`),
    type: 'color'
  }))

  return (
    <Modal
      {...omit(props, 'formData', 'saveEnabled', 'save', 'reset', 'updateProp', 'onSave')}
      icon={icon}
      title={label}
      onEntering={() => {
        props.reset(props.colorChart)
        if (props.colorChart) {
          setColorCount(props.colorChart.colors.length)
        } else {
          setColorCount(1)
        }
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
          },
          ...colorFields
          ]
        }]}
      >
        <Button
          className="btn btn-link btn-sm"
          icon="fa fa-fw fa-plus"
          type={CALLBACK_BUTTON}
          primary={true}
          htmlType="button"
          label={trans('add_color', {}, 'actions')}
          callback={addColor}
        />
        <Button
          className="modal-btn btn btn-primary"
          type={CALLBACK_BUTTON}
          primary={true}
          htmlType="submit"
          label={trans('save', {}, 'actions')}
          disabled={!props.saveEnabled}
          callback={() => {
            const formData = {...props.formData}
            formData.colors = Object.values(formData.colors)
            props.save(formData).then((response) => {
              props.fadeModal()

              if (props.onSave) {
                props.onSave(response)
              }
            })
          }}
        />
      </FormData>
    </Modal>
  )
}

export {
  ColorChartParametersModal
}
