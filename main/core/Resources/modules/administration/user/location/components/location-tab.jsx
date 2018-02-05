import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {Location}  from '#/main/core/administration/user/location/components/location.jsx'
import {Locations} from '#/main/core/administration/user/location/components/locations.jsx'
import {actions}   from '#/main/core/administration/user/location/actions'

const LocationTabActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="locations.current"
      target={(location, isNew) => isNew ?
        ['apiv2_location_create'] :
        ['apiv2_location_update', {id: location.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/locations/form'})}
      open={{
        icon: 'fa fa-plus',
        label: t('add_location'),
        action: '#/locations/form'
      }}
      cancel={{
        action: () => navigate('/locations')
      }}
    />
  </PageActions>

LocationTabActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const LocationTabActions = withRouter(LocationTabActionsComponent)

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
