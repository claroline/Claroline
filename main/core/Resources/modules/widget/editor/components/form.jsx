import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const WidgetForm = props =>
  <FormData
    className="widget-section-form"
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
            label: trans('title')
          }, {
            name: 'visible',
            type: 'boolean',
            label: trans('publish_section', {}, 'widget')
          }
        ]
      }, {
        id: 'display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.alignName',
            label: trans('title_align'),
            type: 'choice',
            displayed: (section) => !!section.name,
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                left: trans('text_left_align'),
                center: trans('center'),
                right: trans('text_right_align')
              }
            }
          }, {
            name: 'display.color',
            label: trans('titleColor'),
            type: 'color',
            displayed: (section) => !!section.name
          }, {
            name: 'display.borderColor',
            label: trans('border'),
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
