import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Icons} from '#/main/core/administration/parameters/icon/components/icons'
import {Icon} from '#/main/core/administration/parameters/icon/containers/icon'

const IconsMain = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/icons',
        exact: true,
        render() {
          const IconsList = (
            <Icons
              path={props.path}
            />
          )

          return IconsList
        }
      }, {
        path: '/icons/form/:id?',
        component: Icon,
        onEnter: (params) => props.openIconSetForm(params.id),
        onLeave: () => props.resetIconSetForm()
      }
    ]}
  />

IconsMain.propTypes = {
  path: T.string,
  openIconSetForm: T.func.isRequired,
  resetIconSetForm: T.func.isRequired
}

export {
  IconsMain
}
