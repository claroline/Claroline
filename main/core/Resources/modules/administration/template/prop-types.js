import {PropTypes as T} from 'prop-types'

const TemplateType = {
  propTypes: {
    id: T.string,
    name: T.string,
    placeholders: T.arrayOf(T.string),
    defaultTemplate: T.string
  }
}

const Template = {
  propTypes: {
    id: T.string,
    name: T.string,
    type: T.shape(TemplateType.propTypes),
    content: T.string,
    lang: T.string
  }
}

export {
  TemplateType,
  Template
}