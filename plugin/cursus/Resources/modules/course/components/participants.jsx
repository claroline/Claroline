import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

const CourseParticipants = (props) =>
  <Fragment>
    <div className="row" style={{marginTop: '-20px'}}>
      <div className="analytics-card">
        <span className="fa fa-chalkboard-teacher" style={{backgroundColor: schemeCategory20c[1]}} />

        <h1 className="h3">
          <small>{trans('Formateurs')}</small>
          5
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-user" style={{backgroundColor: schemeCategory20c[5]}} />

        <h1 className="h3">
          <small>{trans('users')}</small>
          23
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-user-plus" style={{backgroundColor: schemeCategory20c[9]}} />

        <h1 className="h3">
          <small>{trans('Places disponibles')}</small>
          27
        </h1>
      </div>
    </div>

    <div className="row">
      <div className="col-md-3">
        <Vertical
          basePath={props.path+'/'+props.course.slug+'/participants'}
          tabs={[
            {
              icon: 'fa fa-fw fa-chalkboard-teacher',
              title: trans('Formateurs'),
              path: '/',
              exact: true
            }, {
              icon: 'fa fa-fw fa-user',
              title: trans('users'),
              path: '/users',
              exact: true
            }, {
              icon: 'fa fa-fw fa-users',
              title: trans('groups'),
              path: '/groups',
              exact: true
            }, {
              icon: 'fa fa-fw fa-user-plus',
              title: trans('En attente'),
              path: '/pending',
              exact: true
            }
          ]}
        />
      </div>

      <div className="col-md-9">
      </div>
    </div>
  </Fragment>

CourseParticipants.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired
}

export {
  CourseParticipants
}
