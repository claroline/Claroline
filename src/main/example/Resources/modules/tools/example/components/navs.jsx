import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

import {ContentNav} from '#/main/app/content/components/nav'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentMenu} from '#/main/app/content/components/menu'

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

    <hr className="my-5" />
    <ContentTitle title="Horizontal nav" />

    <ContentNav
      className="mb-3"
      type="horizontal"
      path={props.path}
      sections={SampleData}
    />

    <hr className="my-5" />
    <ContentTitle title="Simple menu" />

    <nav>
      <div className="list-group" role="presentation">
        <Button
          className="list-group-item list-group-item-action d-flex align-items-center"
          type={CALLBACK_BUTTON}
          callback={() => true}
          label="Item 1"
        >
          <span className="fa fa-chevron-right text-body-tertiary ms-auto" aria-hidden={true} role="presentation" />
        </Button>
        <Button
          className="list-group-item list-group-item-action d-flex align-items-center"
          type={CALLBACK_BUTTON}
          callback={() => true}
          label="Item 2"
        >
          <span className="fa fa-chevron-right text-body-tertiary ms-auto" aria-hidden={true} role="presentation" />
        </Button>
      </div>

      <div className="fs-sm text-body-secondary text-uppercase fw-semibold mt-3 mb-1">Group 1</div>
      <div className="list-group" role="presentation">
        <Button
          className="list-group-item list-group-item-action d-flex align-items-center"
          type={CALLBACK_BUTTON}
          callback={() => true}
          label="Item 3"
        >
          <span className="fa fa-chevron-right text-body-tertiary ms-auto" aria-hidden={true} role="presentation" />
        </Button>
        <Button
          className="list-group-item list-group-item-action d-flex align-items-center"
          type={CALLBACK_BUTTON}
          callback={() => true}
          label="Item 4"
        >
          <span className="fa fa-chevron-right text-body-tertiary ms-auto" aria-hidden={true} role="presentation" />
        </Button>
      </div>

      <div className="fs-sm text-body-secondary text-uppercase fw-semibold mt-3 mb-1">Group 2</div>
      <div className="list-group" role="presentation">
        <Button
          className="list-group-item list-group-item-action d-flex align-items-center"
          type={CALLBACK_BUTTON}
          callback={() => true}
          label="Item 5"
        >
          <span className="fa fa-chevron-right text-body-tertiary ms-auto" aria-hidden={true} role="presentation" />
        </Button>
      </div>
    </nav>

    <hr className="my-5" />
    <ContentTitle title="Detailled menu" />
    <ContentMenu
      autoFocus={false}
      items={[
        {
          id: 'item-1',
          icon: 'rocket',
          label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
          description: 'Sed dignissim vulputate ante, quis ultrices tellus euismod vel.',
          action: { type: CALLBACK_BUTTON, callback: () => true }
        }, {
          id: 'item-2',
          icon: 'rocket',
          label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
          description: 'Sed dignissim vulputate ante, quis ultrices tellus euismod vel.',
          action: { type: CALLBACK_BUTTON, callback: () => true },
          group: 'Group 1'
        }, {
          id: 'item-3',
          icon: 'rocket',
          label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
          description: 'Sed dignissim vulputate ante, quis ultrices tellus euismod vel.',
          action: { type: CALLBACK_BUTTON, callback: () => true },
          group: 'Group 1'
        }, {
          id: 'item-4',
          icon: 'rocket',
          label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
          description: 'Sed dignissim vulputate ante, quis ultrices tellus euismod vel.',
          action: { type: CALLBACK_BUTTON, callback: () => true },
          group: 'Group 2'
        }, {
          id: 'item-5',
          icon: 'rocket',
          label: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
          description: 'Sed dignissim vulputate ante, quis ultrices tellus euismod vel.',
          action: { type: CALLBACK_BUTTON, callback: () => true },
          group: 'Group 2'
        }
      ]}
    />
  </Fragment>

ExampleNavs.propTypes = {
  path: T.string.isRequired
}

export {
  ExampleNavs
}
