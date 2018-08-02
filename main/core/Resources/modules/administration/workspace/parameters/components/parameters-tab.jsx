import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

import {WorkspaceList} from '#/main/core/workspace/list/components/workspace-list'
import {constants} from '#/main/app/content/list/constants'

const Parameters = () => {
  const choices = {}

  WorkspaceList.definition.forEach(property => {
    choices[property.name] = property.label
  })

  return (
    <FormData
      level={3}
      name="parameters"
      buttons={true}
      target={['apiv2_parameters_update']}
      sections={[
        {
          icon: 'fa fa-fw fa-book',
          title: trans('display'),
          defaultOpened: true,
          fields: [
            {
              name: 'workspace.list.default_mode',
              label: trans('mode'),
              type: 'choice',
              options: {
                multiple: false,
                condensed: false,
                choices: {
                  [constants.DISPLAY_LIST]: trans('list_display_list'),
                  [constants.DISPLAY_LIST_SM]: trans('list_display_list_sm'),
                  [constants.DISPLAY_TABLE]: trans('list_display_table'),
                  [constants.DISPLAY_TABLE_SM]: trans('list_display_table_sm'),
                  [constants.DISPLAY_TILES]: trans('list_display_tiles'),
                  [constants.DISPLAY_TILES_SM]: trans('list_display_tiles_sm')
                }
              },
              displayed: true
            },
            {
              name: 'workspace.list.default_properties',
              label: trans('properties'),
              type: 'choice',
              options: {
                multiple: true,
                condensed: false,
                choices: choices
              },
              displayed: true
            }
          ]
        }
      ]}
    />
  )
}

Parameters.propTypes = {
  parameters: T.object.isRequired
}

const ParametersTab = connect(
  (state) => ({
    parameters: formSelect.data(formSelect.form(state, 'parameters'))
  })
)(Parameters)

export {
  ParametersTab
}
