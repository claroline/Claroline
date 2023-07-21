import React from 'react'

import {trans, transChoice} from '#/main/app/intl'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action'

import {MODAL_PARAMETERS_COLOR_CHART} from '#/main/theme/administration/appearance/modals/color-chart-parameters'

const AppearanceColorCharts = (props) => {
  return (
    <ul className="list-group list-group-striped list-group-flush">
      {props.availableColorCharts.map((color, index) => (
        <li className="list-group-item" key={index}>
          <h3 className="h5 color-chart-title">
            {color.name}
            <Toolbar
              buttonName='btn btn-link'
              tooltip='bottom'
              actions={[
                {
                  name: 'edit',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-pencil',
                  label: trans('edit', {}, 'actions'),
                  modal: [MODAL_PARAMETERS_COLOR_CHART, {
                    colorChart: color,
                    onSave: (data) => props.updateColorChart(data)
                  }],
                  displayed: true
                },
                {
                  name: 'delete',
                  type: ASYNC_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  request: {
                    url: ['apiv2_color_collection_delete_bulk', {ids: [color.id]}],
                    request: {
                      method: 'DELETE'
                    },
                    success: () => props.removeColorChart(color)
                  },
                  confirm: {
                    title: transChoice('color_chart_delete_confirm_title', 1, {}, 'appearance'),
                    subtitle: color.name,
                    message: transChoice('color_chart_delete_confirm_message', 1, {count: 1}, 'appearance')
                  },
                  dangerous: true
                }
              ]}
            />
          </h3>
          <div className="color-chart-colors-list">
            {color.colors.map((color, index) => (
              <div className="color-dot md " style={{backgroundColor: color}} key={index}></div>
            ))}
          </div>
        </li>
      ))}
    </ul>
  )
}

export {
  AppearanceColorCharts
}
