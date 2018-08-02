import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {select} from '#/main/app/content/list/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {trans} from '#/main/core/translation'

const topTypes = {
  top_extension: trans('top_extension'),
  top_workspaces_resources: trans('top_workspaces_resources'),
  top_workspaces_connections: trans('top_workspaces_connections'),
  top_resources_views: trans('top_resources_views'),
  top_resources_downloads: trans('top_resources_downloads'),
  top_users_workspaces_enrolled: trans('top_users_workspaces_enrolled'),
  top_users_workspaces_owners: trans('top_users_workspaces_owners'),
  top_media_views: trans('top_media_views'),
  top_users_connections: trans('top_users_connections')
}

class Tab extends Component {
  getDefinition() {
    const defaultDefinition = [
      {
        name: 'type',
        type: 'choice',
        label: trans('type'),
        displayable: false,
        displayed: false,
        filterable: true,
        options: {
          multiple: false,
          unique: false,
          choices: topTypes
        }
      }, {
        name: 'dateLog',
        type: 'date',
        label: trans('activity_rule_form_activeFrom'),
        displayable: false,
        displayed: false,
        filterable: true,
        options: {
          time: true
        }
      }, {
        name: 'dateTo',
        type: 'date',
        label: trans('activity_rule_form_activeUntil'),
        displayable: false,
        displayed: false,
        filterable: true,
        options: {
          time: true
        }
      }
    ]
    let definition = []
    switch (this.props.type) {
      case 'top_extension':
        definition = [
          {
            name: 'type',
            type: 'string',
            label: trans('type'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'total',
            type: 'string',
            label: trans('total'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false
          }
        ]
        break
      case 'top_workspaces_resources':
        definition = [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'total',
            type: 'string',
            label: trans('total'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false
          }
        ]
        break
      case 'top_workspaces_connections':
        definition = [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'actions',
            type: 'string',
            label: trans('actions'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false
          }
        ]
        break
      case 'top_resources_downloads':
      case 'top_resources_views':
      case 'top_media_views':
        definition = [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'actions',
            type: 'string',
            label: trans('actions'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false
          }
        ]
        break
      case 'top_users_workspaces_owners':
      case 'top_users_workspaces_enrolled':
        definition = [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          }, {
            name: 'total',
            type: 'string',
            label: trans('total'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false
          }
        ]
        break
      case 'top_users_connections':
      default:
        definition = [
          {
            name: 'doer.name',
            type: 'string',
            label: trans('user'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false,
            primary: true
          },
          {
            name: 'actions',
            type: 'string',
            label: trans('actions'),
            filterable: false,
            displayable: true,
            displayed: true,
            sortable: false
          }
        ]
        break
    }

    return definition.concat(defaultDefinition)
  }
  
  render() {
    return (
      <ListData
        name="topActions"
        fetch={{
          url: ['apiv2_admin_tool_analytics_top_actions'],
          autoload: true
        }}
        definition={this.getDefinition()}

        display={{
          available : [listConst.DISPLAY_TABLE],
          current: listConst.DISPLAY_TABLE
        }}
      />
    )
  }
}

Tab.propTypes = {
  type: T.string.isRequired
}

const TabContainer = connect(
  state => ({
    type: select
      .filters(select.list(state, 'topActions'))
      .filter(it => it.property === 'type')
      .reduce((t, i) => i.value || t, '')
  })
)(Tab)

export {
  TabContainer as TopActionsTab
}
