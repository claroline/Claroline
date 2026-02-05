import React from 'react'

import {Resource} from '#/main/core/resource'

import {LessonEditor} from '#/plugin/lesson/resources/lesson/editor/components/main'
import {LessonPlayer} from '#/plugin/lesson/resources/lesson/player/components/main'
import {LessonDashboard} from '#/plugin/lesson/resources/lesson/dashboard/components/main'

const LessonResource = (props) =>
  <Resource
    {...props}
    editor={LessonEditor}
    dashboard={LessonDashboard}
    pages={[
      {
        path: '/',
        component: LessonPlayer
      }
    ]}
  />

export {
  LessonResource
}
