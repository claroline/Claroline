import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {Location}  from '#/main/core/administration/user/location/components/location'
import {Locations} from '#/main/core/administration/user/location/components/locations'
import {actions}   from '#/main/core/administration/user/location/actions'

const LocationTabActions = () =>
  <PageActions>
    <PageAction
      type="link"
      icon="fa fa-plus"
      label={trans('add_location')}
      target="/locations/form"
      primary={true}
    />
  </PageActions>

const LocationTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/locations',
        exact: true,
        component: Locations
      }, {
        path: '/locations/form/:id?',
        component: Location,
        onEnter: (params) => props.openForm(params.id || null)
      }
    ]}
  />

LocationTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const LocationTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('locations.current', id))
    }
  })
)(LocationTabComponent)

export {
  LocationTabActions,
  LocationTab
}
