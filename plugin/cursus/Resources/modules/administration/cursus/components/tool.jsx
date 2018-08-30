import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {ParametersTab} from '#/plugin/cursus/administration/cursus/parameters/components/parameters-tab'
import {SessionTab, SessionTabActions} from '#/plugin/cursus/administration/cursus/session/components/session-tab'
import {CourseTab, CourseTabActions} from '#/plugin/cursus/administration/cursus/course/components/course-tab'
import {CursusTab, CursusTabActions} from '#/plugin/cursus/administration/cursus/cursus/components/cursus-tab'
import {SessionEventTab, SessionEventTabActions} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-tab'

const CursusTool = () =>
  <TabbedPageContainer
    title={trans('claroline_cursus_tool', {}, 'tools')}
    redirect={[
      {from: '/', exact: true, to: '/courses'}
    ]}
    tabs={[
      {
        icon: 'fa fa-tasks',
        title: trans('courses', {}, 'cursus'),
        path: '/courses',
        actions: CourseTabActions,
        content: CourseTab
      }, {
        icon: 'fa fa-cubes',
        title: trans('sessions', {}, 'cursus'),
        path: '/sessions',
        actions: SessionTabActions,
        content: SessionTab
      }, {
        icon: 'fa fa-clock-o',
        title: trans('session_events', {}, 'cursus'),
        path: '/events',
        actions: SessionEventTabActions,
        content: SessionEventTab
      }, {
        icon: 'fa fa-database',
        title: trans('cursus', {}, 'cursus'),
        path: '/cursus',
        actions: CursusTabActions,
        content: CursusTab
      }, {
        icon: 'fa fa-cog',
        title: trans('parameters'),
        path: '/parameters',
        onlyIcon: true,
        content: ParametersTab
      }
    ]}
  />

export {
  CursusTool
}