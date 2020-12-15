import {PropTypes as T} from 'prop-types'

const TemplateType = {
  propTypes: {
    id: T.string,
    name: T.string,
    placeholders: T.arrayOf(T.string),
    defaultTemplate: T.string
  }
}

export {
  TemplateType
}
