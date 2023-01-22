import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {FrameworkTab} from '#/plugin/competency/tools/evaluation/framework/components/framework-tab'
import {ScaleTab} from '#/plugin/competency/tools/evaluation/scale/components/scale-tab'
import {ContentTabs} from '#/main/app/content/components/tabs'

const CompetencyTool = (props) =>
  <ToolPage
    primaryAction="new_framework"
    actions={[
      {
        name: 'new_framework',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('framework.create', {}, 'competency'),
        target: `${props.path}/competencies/frameworks/form`,
        primary: true
      }, {
        name: 'import_framework',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-upload',
        label: trans('framework.import', {}, 'competency'),
        target: `${props.path}/competencies/frameworks/import`
      }, {
        name: 'new_scale',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('scale.create', {}, 'competency'),
        target: `${props.path}/competencies/scales/form`
      }
    ]}
    subtitle={trans('competencies', {}, 'evaluation')}
  >
    <header className="row content-heading">
      <ContentTabs
        sections={[
          {
            name: 'frameworks',
            type: LINK_BUTTON,
            label: trans('frameworks', {}, 'competency'),
            target: `${props.path}/competencies/frameworks`
          }, {
            name: 'scales',
            type: LINK_BUTTON,
            label: trans('scales', {}, 'competency'),
            target: `${props.path}/competencies/scales`
          }
        ]}
      />
    </header>

    <Routes
      path={props.path+'/competencies'}
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