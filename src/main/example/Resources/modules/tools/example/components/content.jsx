import React from 'react'
import classes from 'classnames'

import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentTitle} from '#/main/app/content/components/title'

const ExampleContent = () =>
  <div>
    <ContentTitle title="Content sizing" />

    {['lg', 'md', 'sm'].map(size =>
      <div
        key={size}
        className={classes('my-3 bg-secondary-subtle text-secondary-emphasis d-flex justify-content-center align-items-center', `content-${size}`)}
        style={{height: 120}}
      >
        .content-{size}
      </div>
    )}

    <ContentTitle title="Placeholders" />

    <ContentPlaceholder
      className="mb-3"
      icon="fa fa-fw fa-bomb"
      title="No content found."
      help="This is an additional help text to guide the user to fill the data which will replace the placeholder"
      size="lg"
    />

    <ContentPlaceholder
      className="mb-3"
      title="No content found."
      size="lg"
    />

    <ContentPlaceholder
      className="mb-3"
      title="No content found."
      size="md"
    />

    <ContentPlaceholder
      className="mb-3"
      title="No content found."
      size="sm"
    />
  </div>

export {
  ExampleContent
}
