import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentNav} from '#/main/app/content/components/nav'
import {ContentTitle} from '#/main/app/content/components/title'

const SampleData = [
  {
    id: 'section-1',
    title: 'Section 1',
    path: '/',
    exact: true
  }, {
    id: 'section-2',
    title: 'Section 2',
    path: '/section2'
  }, {
    id: 'section-3',
    title: 'Section 3 (Disabled)',
    path: '/section3',
    disabled: true
  }, {
    id: 'section-4',
    title: 'Section 4',
    path: '/section4'
  }
]

const ExampleNavs = (props) =>
  <Fragment>
    <ContentTitle title="Vertical nav" />

    <div className="row">
      <div className="col-md-3">
        <ContentNav
          className="mb-3"
          type="vertical"
          path={props.path}
          sections={SampleData}
        />
      </div>
    </div>

    <ContentTitle title="Horizontal nav" />

    <ContentNav
      className="mb-3"
      type="horizontal"
      path={props.path}
      sections={SampleData}
    />
  </Fragment>

ExampleNavs.propTypes = {
  path: T.string.isRequired
}

export {
  ExampleNavs
}
