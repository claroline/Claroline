import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {navigate} from '#/main/core/router'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions} from '#/plugin/reservation/administration/resource/actions'

const ResourcesList = props => {
  const choices = {}
  props.resourceTypes.reduce((o, rt) => Object.assign(o, {[rt.name]: rt.name}), choices)

  return (
    <DataListContainer
      name="resources"
      title={trans('resource_types', {}, 'reservation')}
      fetch={{
        url: ['apiv2_reservationresource_list'],
        autoload: true
      }}
      open={{
        action: (row) => `#/form/${row.id}`
      }}
      delete={{
        url: ['apiv2_reservationresource_delete_bulk']
      }}
      definition={[{
        name: 'name',
        label: trans('name', {}, 'platform'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'resourceType.name',
        label: trans('type', {}, 'platform'),
        type: 'enum',
        displayed: true,
        options: {
          choices: choices
        }
      }, {
        name: 'localisation',
        label: trans('location', {}, 'platform'),
        type: 'string',
        displayed: true
      }, {
        name: 'quantity',
        label: trans('quantity', {}, 'reservation'),
        type: 'number',
        displayed: true
      }, {
        name: 'color',
        label: trans('color', {}, 'platform'),
        type: 'string',
        displayed: true
      }]}
      filterColumns={true}
      actions={[
        {
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'platform'),
          action: (rows) => navigate(`/form/${rows[0].id}`),
          context: 'row'
        },
        {
          icon: 'fa fa-w fa-sign-out',
          label: trans('export', {}, 'platform'),
          action: (rows) => props.exportResources(rows)
        }
      ]}
      card={() => ({
        onClick: () => {},
        poster: null,
        icon: null,
        title: '',
        subtitle: '',
        contentText: '',
        flags: [].filter(flag => !!flag),
        footer:
          <span></span>,
        footerLong:
          <span></span>
      })}
    />
  )
}

ResourcesList.propTypes = {
  resourceTypes: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  exportResources: T.func.isRequired
}

const Resources = connect(
  state => ({
    resourceTypes: state.resourceTypes
  }),
  dispatch =>({
    exportResources(resources) {
      dispatch(actions.exportResources(resources))
    }
  })
)(ResourcesList)

export {
  Resources
}
