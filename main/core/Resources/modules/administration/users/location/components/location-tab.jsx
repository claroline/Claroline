import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/core/administration/users/store'
import {Location}  from '#/main/core/administration/users/location/components/location'
import {Locations} from '#/main/core/administration/users/location/components/locations'
import {actions}   from '#/main/core/administration/users/location/store'

const LocationTabActionsComponent = (props) =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_location')}
      target={`${props.path}/locations/form`}
      primary={true}
    />
  </PageActions>

LocationTabActionsComponent.propTypes = {
  path: T.string.isRequired
}

const LocationTabComponent = props =>
  <Routes
    path={props.path}
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
  path: T.string.isRequired,
  openForm: T.func.isRequired
}

const LocationTabActions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(LocationTabActionsComponent)

const LocationTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open(baseSelectors.STORE_NAME+'.locations.current', id))
    }
  })
)(LocationTabComponent)

export {
  LocationTabActions,
  LocationTab
}
