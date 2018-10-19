import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {HtmlText} from '#/main/core/layout/components/html-text'

const SimpleWidget = props => {
  if (props.content) {
    return (
      <HtmlText>{props.content}</HtmlText>
    )
  }

  return (
    <EmptyPlaceholder
      size="lg"
      icon="fa fa-file"
      title={trans('no_content')}
    />
  )
}


SimpleWidget.propTypes = {
  content: T.string
}

export {
  SimpleWidget
}
