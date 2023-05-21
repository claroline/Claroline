import React, {Fragment} from 'react'

import {trans, transChoice} from '#/main/app/intl'
import {ContentTitle} from '#/main/app/content/components/title'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_PARAMETERS_COLOR_CHART} from '#/main/theme/administration/appearance/modals/color-chart-parameters'

const AppearanceColorCharts = (props) => {
  return (
    <Fragment>
      {props.availableColorCharts.map((color, index) => (
        <div key={index}>
          <ContentTitle
            title={trans(color.name)}
            className="h5"
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

          <div className="list-group color-chart-colors-list">
            {color.colors.map((color, index) => (
              <div className="color-chart-dot" style={{backgroundColor: color}} key={index}></div>
            ))}
          </div>
        </div>
      ))}
    </Fragment>
  )
}

export {
  AppearanceColorCharts
}
