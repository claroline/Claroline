import React from 'react'
import {PropTypes as T} from 'prop-types'

import {PageFull} from '#/main/app/page/components/full'

const PageEditor = (props) =>
  <PageFull
    {...props}
  >
    {props.children}
  </PageFull>

PageEditor.propTypes = {

}

export {
  PageEditor
}
