import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentNav} from '#/main/app/content/components/nav'
import {ContentTitle} from '#/main/app/content/components/title'

const SampleData = [
  {
    id: 'section-1',
    title: 'Section 1',
    path: '/section1',
    render: () => <div>Section 1 content</div>
  }, {
    id: 'section-2',
    title: 'Section 2',
    path: '/section2',
    render: () => <div>Section 2 content</div>
  }, {
    id: 'section-3',
    title: 'Section 3 (Disabled)',
    path: '/section3',
    disabled: true,
    render: () => <div>Section 3 content</div>
  }, {
    id: 'section-4',
    title: 'Section 4',
    path: '/section4',
    render: () => <div>Section 4 content</div>
  }
]

const ExampleNavs = (props) =>
  <Fragment>
    <ContentTitle title="Vertical nav" />

    <ContentNav
      path={props.path+'/vertical'}
      redirect={[
        {from: '/', exact: true, to: '/section1'}
      ]}
      sections={SampleData}
    />

    <ContentTitle title="Horizontal nav" />
  </Fragment>

ExampleNavs.propTypes = {
  path: T.string.isRequired
}

export {
  ExampleNavs
}
