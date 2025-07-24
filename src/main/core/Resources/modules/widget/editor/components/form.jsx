import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Form, FormContent, selectors as formSelectors} from '#/main/app/content/form'

const WidgetForm = (props) => {
  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, props.name)))
  const saveEnabled = useSelector((state) => formSelectors.saveEnabled(formSelectors.form(state, props.name)))

  return (
    <Form
      name={props.name}
      flush={true}
      level={2}
      displayLevel={5}
    >
      <FormContent
        className="modal-body"
        name={props.name}
        level={2}
        displayLevel={5}
        flush={true}
        definition={[
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

      <div className="modal-footer mt-n5">
        {props.children}

        <Button
          type={CALLBACK_BUTTON}
          label={trans(props.isNew ? 'add_section' : 'save_section', {}, 'actions')}
          className="btn btn-primary"
          htmlType="submit"
          disabled={!saveEnabled}
          callback={() => props.onSave(formData)}
        />
      </div>
    </Form>
  )
}

WidgetForm.propTypes = {
  level: T.number,
  name: T.string.isRequired,
  children: T.node
}

export {
  WidgetForm
}
