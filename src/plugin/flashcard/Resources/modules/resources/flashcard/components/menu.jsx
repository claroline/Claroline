import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const FlashcardDeckMenu = props =>
  <MenuSection
    {...omit(props, 'path', 'editable')}
    title={trans('flashcard', {}, 'resource')}
  />

FlashcardDeckMenu.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
}

export {
  FlashcardDeckMenu
}
