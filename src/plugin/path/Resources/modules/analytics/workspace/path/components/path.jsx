import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans, transChoice} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DataCard} from '#/main/app/data/components/card'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {ResourceIcon} from '#/main/core/resource/components/icon'
import {BarChart} from '#/main/core/layout/chart/bar/components/bar-chart'
import {MODAL_RESOURCE_PARTICIPANTS} from '#/plugin/path/analytics/workspace/path/modals/participants'

const Path = (props) =>
  <article className="path-tracking">
    <DataCard
      orientation="row"
      size="sm"
      id={props.path.id}
      poster={props.path.thumbnail ? asset(props.path.thumbnail.url) : null}
      icon={
        <ResourceIcon className="icon" mimeType={props.path.meta.mimeType} />
      }
      title={props.path.name}
      flags={[
        ['fa fa-fw fa-eye', transChoice('resource_views', props.path.meta.views, {count: props.path.meta.views}, 'resource'), props.path.meta.views],
        props.path.social && ['fa fa-fw fa-thumbs-up', transChoice('resource_likes', props.path.social.likes, {count: props.path.social.likes}, 'resource'), props.path.social.likes]
      ].filter(flag => !!flag)}
      contentText={props.path.meta.description}
      actions={[
        {
          name: 'open',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-external-link',
          label: trans('open', 'actions'),
          target: resourceRoute(props.path)
        }, {
          name: 'participants',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-user',
          label: trans('show-participants', {}, 'actions'),
          modal: [MODAL_RESOURCE_PARTICIPANTS, {
            resourceNode: props.path
          }]
        }
      ]}
    />
    <div className="path-tracking-current">
      <BarChart
        height={200}
        width={1400}
        data={props.steps.reduce((acc, stepData) => {
          acc[stepData.step.id] = {
            xData: stepData.step.title,
            yData: stepData.users.length
          }

          return acc
        }, {not_started: {xData: trans('not_started', {}, 'analytics'), yData: props.unstartedUsers.length}})}
        yAxisLabel={{
          show: true,
          text: trans('users_count')
        }}
        xAxisLabel={{
          show: true,
          text: trans('steps', {}, 'path')
        }}
        onClick={(data, idx) => {
          if (0 === idx) {
            props.showStepDetails(props.unstartedUsers)
          } else if (props.steps[idx - 1] && props.steps[idx - 1].users) {
            props.showStepDetails(props.steps[idx - 1].users)
          }
        }}
        margin={{
          left: 50,
          top: 5,
          right: 1,
          bottom: 50
        }}
      />
    </div>
  </article>

Path.propTypes = {
  path: T.shape(
    ResourceNodeTypes.propTypes
  ),
  steps: T.arrayOf(T.shape({
    step: T.shape({
      id: T.string,
      title: T.string
    }),
    users: T.arrayOf(T.shape({
      id: T.string,
      username: T.string,
      firstName: T.string,
      lastName: T.string,
      name: T.string
    }))
  })),
  unstartedUsers: T.arrayOf(T.shape({
    id: T.string,
    username: T.string,
    firstName: T.string,
    lastName: T.string,
    name: T.string
  })),
  showStepDetails: T.func.isRequired
}

Path.defaultProps = {
  steps: []
}

export {
  Path
}
