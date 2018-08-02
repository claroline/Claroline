import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {actions} from '#/plugin/reservation/administration/resource/actions'

// TODO : add card display

const ResourcesList = props =>
  <ListData
    name="resources"
    title={trans('resource_types', {}, 'reservation')}
    fetch={{
      url: ['apiv2_reservationresource_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `/form/${row.id}`
    })}
    delete={() => ({
      type: URL_BUTTON,
      target: ['apiv2_reservationresource_delete_bulk']
    })}
    definition={[{
      name: 'name',
      label: trans('name', {}, 'platform'),
      type: 'string',
      primary: true,
      displayed: true
    }, {
      name: 'resourceType.name',
      label: trans('type', {}, 'platform'),
      type: 'choice',
      displayed: true,
      options: {
        choices: props.resourceTypes.reduce((o, rt) => Object.assign(o, {[rt.name]: rt.name}), {})
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
    actions={(rows) => [
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-w fa-sign-out',
        label: trans('export', {}, 'platform'),
        callback: () => props.exportResources(rows)
      }
    ]}
  />

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
