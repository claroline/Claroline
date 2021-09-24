import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const BlogMenu = props =>
  <MenuSection
    {...props}
    title={trans('icap_blog', {}, 'resource')}
  />

BlogMenu.propTypes = {
  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  BlogMenu
}
