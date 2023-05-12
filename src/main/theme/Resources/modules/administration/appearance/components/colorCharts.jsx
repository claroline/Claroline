import React from 'react'

import {trans, transChoice} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_EDIT_COLOR_CHART} from '#/main/theme/administration/appearance/modals/color-chart-edit'

const AppearanceColorCharts = (props) => {
  return (
    <div>
      {props.availableColorCharts.map((color, index) => (
        <div className="color-chart" key={index}>
          <h3 className="h4 color-chart-title">
            <div className="color-chart-name">
              {trans(color.name)}
            </div>

            <div className="color-colors-list">
              {color.colors.map((color, index) => (
                <div className="color-pastille" style={{backgroundColor: color}} key={index}></div>
              ))}
            </div>

            <Toolbar
              style={{marginLeft: 'auto'}}
              buttonName="btn btn-link btn-sm"
              tooltip="bottom"
              actions={[
                {
                  name: 'edit',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-pencil',
                  label: trans('edit', {}, 'actions'),
                  modal: [MODAL_EDIT_COLOR_CHART, {
                    colorChart: color,
                    onSave: (data) => props.updateColorChart(data)
                  }],
                  displayed: true,
                  group: trans('color_chart', {}, 'appearance')
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
        </div>
      ))}
    </div>
  )
}

export {
  AppearanceColorCharts
}
