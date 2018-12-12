import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {FrameworkTabActions, FrameworkTab} from '#/plugin/competency/administration/competency/framework/components/framework-tab'
import {ScaleTabActions, ScaleTab} from '#/plugin/competency/administration/competency/scale/components/scale-tab'

const CompetencyTool = () =>
  <TabbedPageContainer
    title={trans('competencies', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/frameworks'}
    ]}
    tabs={[
      {
        icon: 'fa fa-graduation-cap',
        title: trans('competencies', {}, 'tools'),
        path: '/frameworks',
        actions: FrameworkTabActions,
        content: FrameworkTab
      }, {
        icon: 'fa fa-arrow-up',
        title: trans('scales', {}, 'competency'),
        path: '/scales',
        actions: ScaleTabActions,
        content: ScaleTab
      }
    ]}
  />

export {
  CompetencyTool
}