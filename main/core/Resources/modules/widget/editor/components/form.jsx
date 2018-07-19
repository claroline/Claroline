import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'

const WidgetForm = props =>
  <FormContainer
    level={props.level}
    name={props.name}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'display.layout',
            type: 'string',
            label: trans('widget_layout'),
            readOnly: true,
            hideLabel: true,
            render: (widget) => {
              const layout = get(widget, 'display.layout') || [1]

              const LayoutPreview = 
                <div className="widget-layout-preview">
                  <div className="row">
                    {times(layout.length, col =>
                      <div key={col} className={`widget-col col-md-${(12 / sum(layout)) * layout[col]}`}>
                        <div className="widget-col-preview"></div>
                      </div>
                    )}
                  </div>
                </div>
              
              return LayoutPreview
            }
          }, {
            name: 'name',
            type: 'string',
            label: trans('name')
          }
        ]
      }, {
        id: 'display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.color',
            label: trans('textColor'),
            type: 'color'
          }, {
            name: 'display.backgroundType',
            label: trans('background'),
            type: 'choice',
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                none: trans('none'),
                color: trans('color'),
                image: trans('image')
              }
            },
            linked: [
              {
                name: 'display.background',
                label: trans('backgroundImage'),
                type: 'image',
                required: true,
                displayed: (widget) => widget.display && 'image' === widget.display.backgroundType
              }, {
                name: 'display.background',
                label: trans('backgroundColor'),
                type: 'color',
                required: true,
                displayed: (widget) => widget.display && 'color' === widget.display.backgroundType
              }
            ]
          }
        ]
      }, {
        id: 'parameters',
        icon: 'fa fa-fw fa-cog',
        title: trans('parameters'),
        fields: [

        ]
      }
    ]}
  />

WidgetForm.propTypes = {
  level: T.number,
  name: T.string.isRequired
}

export {
  WidgetForm
}
