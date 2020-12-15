import {PropTypes as T} from 'prop-types'

import {TemplateType} from '#/main/core/data/types/template-type/prop-types'

const Template = {
  propTypes: {
    id: T.string,
    name: T.string,
    type: T.shape(
      TemplateType.propTypes
    ),
    content: T.string,
    lang: T.string
  }
}

export {
  Template
}
