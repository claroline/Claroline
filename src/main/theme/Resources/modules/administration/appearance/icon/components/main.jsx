import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Icons} from '#/main/theme/administration/appearance/icon/components/icons'
import {Icon} from '#/main/theme/administration/appearance/icon/containers/icon'

const IconMain = (props) =>
  <Routes
    path={props.path+'/appearance'}
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

IconMain.propTypes = {
  path: T.string,
  openIconSetForm: T.func.isRequired,
  resetIconSetForm: T.func.isRequired
}

export {
  IconMain
}
