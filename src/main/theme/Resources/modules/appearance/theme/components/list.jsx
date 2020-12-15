import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/tanslation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/theme/appearance/store'

const ThemeList = (props) =>
  <ListData
    name={selectors.STORE_NAME+'.themes'}
    fetch={{
      url: ['apiv2_theme_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/${row.id}`
    })}
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        primary: true,
        displayed: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true
      }, {
        name: 'meta.plugin',
        label: trans('plugin'),
        displayed: true
      }, {
        name: 'meta.enabled',
        type: 'boolean',
        label: trans('enabled'),
        displayed: true
      }, {
        name: 'meta.default',
        type: 'boolean',
        label: trans('default'),
        displayed: true
      }, {
        name: 'current',
        type: 'boolean',
        label: trans('theme_current', {}, 'theme'),
        displayed: true
      }
    ]}
  />

ThemeList.propTypes = {
  path: T.string.isRequired
}

export {
  ThemeList
}
