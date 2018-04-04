import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/prop-types'

const MODAL_EDIT_WIDGET = 'MODAL_EDIT_WIDGET'

const EditWidgetModal = props =>
  <DataFormModal
    {...props}
    title={trans('edit_widget', {}, 'widget')}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'type',
            type: 'translation',
            label: trans('widget'),
            readOnly: true,
            hideLabel: true,
            options: {
              domain: 'widget'
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
            type: 'enum',
            required: true,
            options: {
              noEmpty: true,
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
                displayed: (widget) => widget.display && 'image' === widget.display.backgroundType,
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

EditWidgetModal.propTypes = {
  data: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,
  save: T.func.isRequired
}

export {
  MODAL_EDIT_WIDGET,
  EditWidgetModal
}
