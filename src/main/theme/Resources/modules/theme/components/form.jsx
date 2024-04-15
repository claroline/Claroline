import React from 'react'
import { useHistory } from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {constants} from '#/main/theme/constants'

const ThemeForm = props =>
  <FormData
    className={props.className}
    level={3}
    name={props.name}
    buttons={true}
    target={(theme, isNew) => isNew ?
      ['apiv2_theme_create'] :
      ['apiv2_theme_update', {id: theme.id}]
    }
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'logo',
            type: 'image',
            label: trans('logo')
          }, {
            name: 'themeMode',
            type: 'choice',
            label: trans('theme_mode', {}, 'appearance'),
            required: true,
            options: {
              condensed: false,
              noEmpty: true,
              choices: constants.MODES
            },
            //calculated: (data) => !data.themeMode ? constants.MODE_AUTO : data.themeMode
          }, {
            name: 'fontSize',
            type: 'choice',
            label: trans('font_size', {}, 'appearance'),
            required: true,
            options: {
              condensed: false,
              noEmpty: true,
              choices: constants.FONT_SIZES
            }
          }, {
            name: 'fontWeight',
            type: 'choice',
            label: trans('font_weight', {}, 'appearance'),
            required: true,
            options: {
              condensed: false,
              choices: constants.FONT_WEIGHTS
            },
            calculated: (data) => parseInt(data.fontWeight)
          }, {
            name: 'primaryColor',
            type: 'color',
            label: trans('primary_color', {}, 'appearance'),
          }, {
            name: 'secondaryColor',
            type: 'color',
            label: trans('secondary_color', {}, 'appearance')
          }
        ]
      }
    ]}
  >
    {props.children}
  </FormData>

ThemeForm.propTypes = {
  name: T.string.isRequired
}

export {
  ThemeForm
}
