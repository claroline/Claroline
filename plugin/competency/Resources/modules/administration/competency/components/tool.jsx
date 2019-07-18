import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {FrameworkTab} from '#/plugin/competency/administration/competency/framework/components/framework-tab'
import {ScaleTab} from '#/plugin/competency/administration/competency/scale/components/scale-tab'

const CompetencyTool = (props) =>
  <ToolPage
    actions={[
      {
        name: 'new_framework',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('framework.create', {}, 'competency'),
        target: `${props.path}/frameworks/form`,
        primary: true,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/frameworks`, exact: true})
      }, {
        name: 'import_framework',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-upload',
        label: trans('framework.import', {}, 'competency'),
        target: `${props.path}/frameworks/import`,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/frameworks`, exact: true})
      }, {
        name: 'new_scale',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('scale.create', {}, 'competency'),
        target: `${props.path}/scales/form`,
        primary: true,
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/scales`, exact: true})
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {
            path: '/frameworks',
            render: () => trans('competencies', {}, 'tools'),
            exact: true
          }, {
            path: '/scales',
            render: () => trans('scales', {}, 'competency')
          }
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/frameworks'}
      ]}
      routes={[
        {
          path: '/frameworks',
          component: FrameworkTab
        }, {
          path: '/scales',
          component: ScaleTab
        }
      ]}
    />
  </ToolPage>

CompetencyTool.propTypes = {
  location: T.object.isRequired,
  path: T.string.isRequired
}

export {
  CompetencyTool
}