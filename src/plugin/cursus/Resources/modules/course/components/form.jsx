import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router/components/routes'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTabs} from '#/main/app/content/components/tabs'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CourseParameters} from '#/plugin/cursus/course/components/parameters'
import {CourseRegistration} from '#/plugin/cursus/course/components/registration'
import {route} from '#/plugin/cursus/routing'

const CourseForm = (props) => {
  const basePath = props.isNew ? props.path + '/new' : route(props.course)+'/edit'

  return (
    <Fragment>
      <div className="row content-heading">
        <ContentTabs
          sections={[
            {
              name: 'parameters',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-cog',
              label: trans('parameters'),
              target: `${basePath}`,
              exact: true
            }, {
              name: 'registration',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-user-plus',
              label: trans('registration'),
              target: `${basePath}/registration`
            }
          ]}
        />
      </div>

      <Routes
        path={basePath}
        routes={[
          {
            path: '/',
            exact: true,
            render: () => <CourseParameters {...props} />
          }, {
            path: '/registration',
            render: () => <CourseRegistration {...props} />
          }
        ]}
      />
    </Fragment>
  )
}

CourseForm.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,

  // from store
  isNew: T.bool.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  update: T.func.isRequired
}

export {
  CourseForm
}